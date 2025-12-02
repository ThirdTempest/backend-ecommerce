<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'line1' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.name' => 'required|string',
            'total' => 'required|numeric',
            'payment_method' => 'required|string|in:cod,paymongo'
        ]);

        $orderNumber = 'ORD-' . strtoupper(Str::random(10));
        $fullAddress = "{$validated['line1']}, {$validated['city']}, {$validated['postal_code']}";

        // 2. Start Database Transaction
        try {
            $order = DB::transaction(function () use ($request, $validated, $orderNumber, $fullAddress) {

                // Create Order Record (Status defaults to 'pending')
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'order_number' => $orderNumber,
                    'status' => 'pending', // IMPORTANT: Stays pending until payment confirmed
                    'total_amount' => $validated['total'],
                    'payment_method' => $validated['payment_method'],
                    'shipping_address' => $fullAddress,
                    'billing_address' => $fullAddress,
                    'phone' => $validated['phone']
                ]);

                // Process Items & DECREMENT STOCK
                foreach ($validated['items'] as $itemData) {
                    $product = Product::lockForUpdate()->find($itemData['id']);

                    if (!$product || $product->stock < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for: " . $itemData['name']);
                    }

                    $product->decrement('stock', $itemData['quantity']);

                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['id'],
                        'quantity' => $itemData['quantity'],
                        'price_at_purchase' => $itemData['price']
                    ]);
                }

                return $order;
            });

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 400);
        }

        // --- OPTION A: PayMongo Flow ---
        if ($request->payment_method === 'paymongo') {

            $lineItems = [];
            $subtotal = 0;

            foreach ($request->items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
                $lineItems[] = [
                    'currency' => 'PHP',
                    'amount' => (int)($item['price'] * 100),
                    'description' => $item['name'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'images' => [$item['image'] ?? 'https://placehold.co/100']
                ];
            }

            $shippingFee = $request->total - $subtotal;
            if ($shippingFee > 0.01) {
                $lineItems[] = [
                    'currency' => 'PHP',
                    'amount' => (int)($shippingFee * 100),
                    'description' => 'Standard Delivery',
                    'name' => 'Shipping Fee',
                    'quantity' => 1,
                    'images' => ['https://cdn-icons-png.flaticon.com/512/709/709790.png']
                ];
            }

           $frontendUrl = env('FRONTEND_URL', 'http://localhost:9000');

            // Call PayMongo
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode(env('PAYMONGO_SECRET_KEY')),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'billing' => [
                            'name' => $validated['name'],
                            'email' => $validated['email'],
                            'phone' => $validated['phone']
                        ],
                        'line_items' => $lineItems,
                        'payment_method_types' => ['gcash', 'card', 'grab_pay', 'paymaya'],

                        // FIX: Point to Vercel URL
                        'success_url' => "{$frontendUrl}/#/checkout/success?order_number={$orderNumber}",
                        'cancel_url' => "{$frontendUrl}/#/checkout/cancel?order_id={$order->id}",

                        'description' => "Order #{$orderNumber}",
                        'reference_number' => $orderNumber
                    ]
                ]
            ]);

            if ($response->successful()) {
                // REMOVED: $order->update(['status' => 'processing']);
                // We leave it as 'pending' until webhook confirms or user completes it.

                $checkoutUrl = $response->json()['data']['attributes']['checkout_url'];
                return response()->json([
                    'message' => 'Redirecting to payment...',
                    'checkout_url' => $checkoutUrl,
                    'type' => 'paymongo'
                ]);
            } else {
                // Restore stock if API call fails
                foreach ($order->items as $detail) {
                    $detail->product->increment('stock', $detail->quantity);
                }
                $order->delete();
                return response()->json(['message' => 'Payment Gateway Error: ' . $response->body()], 500);
            }
        }

        // --- OPTION B: Cash on Delivery (COD) Flow ---
        $order->update(['status' => 'processing']);

        return response()->json([
            'message' => 'Order placed successfully!',
            'order_number' => $orderNumber,
            'type' => 'cod'
        ]);
    }
}

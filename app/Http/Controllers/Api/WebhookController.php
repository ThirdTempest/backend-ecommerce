<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify the event comes from PayMongo (Basic Check)
        // In production, you should verify the 'Paymongo-Signature' header.
        $events = $request->all()['data']['attributes']['type'] ?? null;
        $data = $request->all()['data']['attributes']['data'] ?? null;

        if (!$events || !$data) {
            return response()->json(['status' => 'invalid payload'], 400);
        }

        // Get the Reference Number we sent (Order Number)
        // PayMongo sends it back in 'description' or 'reference_number' depending on setup
        // But the surest way is finding the order by the Checkout Session ID if stored,
        // OR using the description if we formatted it as "Order #ORD-..."

        $description = $data['attributes']['description'] ?? '';
        // Extract ORD-XXXXX from "Order #ORD-XXXXX"
        $orderNumber = str_replace('Order #', '', $description);

        $order = Order::where('order_number', $orderNumber)->with('items.product')->first();

        if (!$order) {
            Log::error("Webhook: Order not found for {$orderNumber}");
            return response()->json(['status' => 'order not found'], 404);
        }

        Log::info("Webhook Received: {$events} for Order {$orderNumber}");

        try {
            DB::beginTransaction();

            switch ($events) {
                case 'checkout_session.payment.paid':
                    // Payment Successful
                    if ($order->status !== 'completed' && $order->status !== 'shipped') {
                        $order->update(['status' => 'processing']);
                    }
                    break;

                case 'checkout_session.payment.failed':
                case 'checkout_session.expired':
                    // Payment Failed/Expired -> Cancel Order & Restore Stock
                    if ($order->status === 'pending') {

                        // Restore Stock
                        foreach ($order->items as $item) {
                            if ($item->product) {
                                $item->product->increment('stock', $item->quantity);
                            }
                        }

                        $order->update(['status' => 'cancelled']);
                    }
                    break;
            }

            DB::commit();
            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}

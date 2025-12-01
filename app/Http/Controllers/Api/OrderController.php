<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB; // Import DB

class OrderController extends Controller
{
    // 1. Fetch "Recent" orders
    public function recent(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
                       ->with('items.product')
                       ->latest()
                       ->take(2)
                       ->get();

        return response()->json($orders);
    }

    // 2. Fetch "All" orders
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
                       ->with('items.product')
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    // 3. Cancel an Order & RESTORE STOCK
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
                      ->with('items.product') // Load items to restore stock
                      ->findOrFail($id);

        if (in_array($order->status, ['pending', 'processing'])) {

            DB::transaction(function () use ($order) {
                // Loop through items and increment stock back
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                $order->update(['status' => 'cancelled']);
            });

            return response()->json(['message' => "Order #{$order->order_number} has been cancelled and stock restored."]);
        }

        return response()->json(['message' => 'Cannot cancel this order.'], 400);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order; // FIX: Import Order model

class DashboardController extends Controller
{
    public function stats()
    {
        // FIX: Fetch REAL counts from the database
        $productCount = Product::count();

        // Count orders that need attention (pending or processing)
        $pendingOrders = Order::whereIn('status', ['pending', 'processing'])->count();

        $lowStockCount = Product::where('stock', '<=', 10)->count();

        // Fetch 5 most recent products for the Quick List
        $recentProducts = Product::latest()->take(5)->get();

        return response()->json([
            'product_count' => $productCount,
            'pending_orders_count' => $pendingOrders,
            'low_stock_count' => $lowStockCount,
            'recent_products' => $recentProducts
        ]);
    }
}

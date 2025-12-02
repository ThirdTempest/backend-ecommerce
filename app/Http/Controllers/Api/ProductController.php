<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    // Fetch products with Filtering and Searching
    public function index(Request $request)
    {
        // Start a query builder
        $query = Product::query();

        // 1. Handle Category Filter (e.g. /api/products?category=Men)
        if ($request->has('category') && $request->category != null) {
            $query->where('category', $request->category);
        }

        // 2. Handle Search (e.g. /api/products?search=Shirt)
        if ($request->has('search') && $request->search != null) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Handle Sale Filter (FIX for Sale Page)
        // Usage: /api/products?on_sale=true
        if ($request->has('on_sale') || $request->has('sale')) {
            $query->whereNotNull('sale_price')
                  ->whereColumn('sale_price', '<', 'price');
        }

        // 4. Sort by newest
        $query->latest();

        // Return 12 items per page
        return ProductResource::collection($query->paginate(12));
    }

    // Fetch single product
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }

    // Fetch New Arrivals (Home Page)
    public function newArrivals()
    {
        // Get the 8 latest products
        $products = Product::latest()->take(8)->get();
        return ProductResource::collection($products);
    }
}

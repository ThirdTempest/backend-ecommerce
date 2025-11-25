<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\DB; // Needed for robust queries

class AdminController extends Controller
{
    // Low Stock Threshold
    private $lowStockThreshold = 10;

    /**
     * Show the Admin Dashboard Index.
     */
    public function index()
    {
        // 1. Total Products (REAL DB COUNT)
        $productCount = Product::count();
        
        // 2. Pending Orders (FIXED: Counting both pending and processing)
        $pendingOrdersCount = Order::whereIn('status', ['pending', 'processing'])->count(); 

        // 3. Low Stock Alerts (REAL DB CALCULATION based on threshold)
        $lowStockCount = Product::where('stock', '<=', $this->lowStockThreshold)->count();

        // 4. Product Quick List
        $recentProducts = Product::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.index', compact(
            'productCount', 
            'pendingOrdersCount', 
            'lowStockCount',
            'recentProducts'
        ));
    }

    // --- PRODUCT MANAGEMENT ---

    /**
     * Display a listing of all products (Admin View), handles searching.
     */
    public function indexProducts(Request $request)
    {
        $query = Product::query();
        $searchQuery = $request->input('query');

        if ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('category', 'like', '%' . $searchQuery . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        return view('admin.products.index', compact('products', 'searchQuery'));
    }

    public function createProduct()
    {
        return view('admin.products.create');
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|lt:price',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);
        
        $imagePath = $request->file('image')->store('products', 'public');
        
        Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . time(),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'],
            'stock' => $validated['stock'],
            'category' => $validated['category'],
            'image_url' => $imagePath,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product added successfully!');
    }

    public function editProduct(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|lt:price',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $dataToUpdate = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'],
            'stock' => $validated['stock'],
            'category' => $validated['category'],
        ];

        if ($request->file('image')) {
            if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                 Storage::disk('public')->delete($product->image_url);
            }

            $newImagePath = $request->file('image')->store('products', 'public');
            $dataToUpdate['image_url'] = $newImagePath;
        }

        $product->update($dataToUpdate);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }
    
    // --- ORDER MANAGEMENT ---

    /**
     * Display low stock products.
     */
    public function lowStockAlerts()
    {
        $products = Product::where('stock', '<=', $this->lowStockThreshold)
                            ->orderBy('stock', 'asc')
                            ->paginate(15);
                            
        $lowStockThreshold = $this->lowStockThreshold;

        return view('admin.products.lowStock', compact('products', 'lowStockThreshold'));
    }

    /**
     * Display all orders for admin management.
     */
    public function indexOrders()
    {
        $orders = Order::with('user', 'items.product')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display details of a specific order. (NEW)
     */
    public function showOrder(Order $order)
    {
        // Eager load needed data before passing to view
        $order->load('user', 'items.product'); 
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the status of a specific order.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled', 
        ]);
        $order->update(['status' => $validated['status']]);
        return back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst($validated['status']) . ".");
    }
}
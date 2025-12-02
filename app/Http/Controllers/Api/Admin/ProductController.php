<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
        }

        return ProductResource::collection($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        try {
            // 1. Validation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category' => 'required|string',
                'description' => 'nullable|string',
                'image' => 'required|image|max:2048'
            ]);

            // 2. Handle Image Upload
            if ($request->hasFile('image')) {
                // Ensure the folder exists
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                $path = $request->file('image')->store('products', 'public');

                if (!$path) {
                    throw new \Exception("Failed to save image to storage.");
                }

                $validated['image_url'] = $path;
            }

            // 3. Clean up array for Database
            unset($validated['image']);

            // 4. Create Product (Wrap in Try-Catch to see DB errors)
            // Force fill all attributes (bypasses $fillable protection for debugging)
            $product = new Product();
            $product->forceFill($validated);
            $product->save();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => new ProductResource($product)
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // CATCH DATABASE ERRORS (Column missing, wrong type, etc.)
            Log::error('Product DB Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Database Error: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ], 500);

        } catch (\Exception $e) {
            // CATCH ALL OTHER ERRORS
            Log::error('Product Create Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function show($id)
    {
        return new ProductResource(Product::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'sale_price' => 'nullable|numeric',
                'stock' => 'required|integer',
                'category' => 'required|string',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('image')) {
                if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                    Storage::disk('public')->delete($product->image_url);
                }
                $path = $request->file('image')->store('products', 'public');
                $validated['image_url'] = $path;
            }

            unset($validated['image']);

            // Use forceFill to bypass $fillable issues during update too
            $product->forceFill($validated);
            $product->save();

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => new ProductResource($product)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function lowStock()
    {
        $threshold = 10;
        $products = Product::where('stock', '<=', $threshold)->paginate(10);
        return ProductResource::collection($products);
    }
}

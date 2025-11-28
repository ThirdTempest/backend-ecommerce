@extends('layouts.app')

@section('title', 'New Arrivals')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4 text-center">
        Fresh Stock: New Arrivals
    </h1>
    <p class="text-xl text-gray-600 dark:text-gray-400 mb-10 text-center max-w-2xl mx-auto">
        Be the first to explore our latest collection added to the store.
    </p>

    <!-- Product Grid (Using standard shop grid for consistent card size) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        
        @forelse ($products as $product)
            <!-- Start of Product Card (FULL DARK MODE INTERACTIVITY APPLIED) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition duration-300 hover:shadow-2xl transform hover:-translate-y-1 relative 
                        dark:border dark:border-gray-700 dark:hover:border-primary">
                
                <!-- NEW BADGE -->
                <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md z-10">
                    NEW
                </span>
                
                <!-- SALE BADGE (Show if on sale) -->
                @if ($product->sale_price !== null)
                    <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md z-10">
                        SALE!
                    </span>
                @endif

                <!-- Product Image -->
                <img src="{{ asset('storage/' . $product->image_url) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                
                <!-- Content Container -->
                <div class="p-5 w-full"> 
                    <!-- Product Category -->
                    <p class="text-sm font-medium text-primary uppercase tracking-wider mb-1">{{ $product->category }}</p>
                    
                    <!-- Product Name -->
                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 truncate" title="{{ $product->name }}">
                        {{ $product->name }}
                    </h4>
                    
                    <!-- Price and Button Row -->
                    <div class="flex items-end justify-between mt-4"> 
                        
                        <!-- Price Display -->
                        <div class="flex-grow">
                            @if ($product->sale_price !== null)
                                <!-- Display Sale Price -->
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-sm font-medium text-gray-500 line-through dark:text-gray-400">
                                        ₱{{ number_format($product->price, 2) }}
                                    </span>
                                    <span class="text-2xl font-bold text-red-600 dark:text-red-400">
                                        ₱{{ number_format($product->sale_price, 2) }}
                                    </span>
                                </div>
                            @else
                                <!-- Display Regular Price -->
                                <span class="text-2xl font-bold text-gray-800 dark:text-white">
                                    ₱{{ number_format($product->price, 2) }}
                                </span>
                            @endif

                            <!-- Stock Indicator (NEW) -->
                            @if ($product->stock <= 0)
                                <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">OUT OF STOCK</p>
                            @elseif ($product->stock <= 5)
                                <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 mt-1">Low Stock ({{ $product->stock }})</p>
                            @endif
                        </div>
                        
                        <!-- Action Button (Form) - Ensures consistent button size/alignment -->
                        <form action="{{ route('cart.add') }}" method="POST" class="w-24 flex-shrink-0"> 
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            @if ($product->stock > 0)
                                <button type="submit" class="bg-primary text-white text-base py-2 px-2 rounded-full hover:bg-green-700 transition duration-300 shadow-md w-full">
                                    Add to Cart
                                </button>
                            @else
                                <button type="button" disabled class="bg-gray-400 text-white text-base py-2 px-2 rounded-full shadow-md cursor-not-allowed w-full">
                                    Out of Stock
                                </button>
                            @endif

                        </form>
                    </div>
                </div>
            </div>
            <!-- End of Product Card -->
        @empty
            <p class="col-span-full text-center text-gray-500 dark:text-gray-400 text-lg">No products have been added yet.</p>
        @endforelse

    </div>

    <!-- View All Products Link -->
    <div class="mt-12 text-center">
        <a href="{{ route('shop') }}" class="text-lg font-medium text-primary hover:underline">
            View All Products &rarr;
        </a>
    </div>
</div>
@endsection
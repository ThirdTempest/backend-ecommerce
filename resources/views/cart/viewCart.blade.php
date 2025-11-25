@extends('layouts.app')

@section('title', 'Shopping Cart & Checkout')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-8">
        Your Shopping Cart
    </h1>

    @if (session('success'))
        <div class="bg-green-100 dark:bg-green-900/50 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-300 p-4 mb-6 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (!empty($cart))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Cart Items List (2/3 width) -->
            <div class="lg:col-span-2 space-y-4">
                @foreach ($cart as $id => $item)
                    <div class="flex items-center bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md border border-gray-100 dark:border-gray-700">
                        <!-- Image -->
                        <img src="{{ asset('storage/' . $item['image_url']) }}" alt="{{ $item['name'] }}" class="w-20 h-20 object-cover rounded-lg mr-4">
                        
                        <!-- Details -->
                        <div class="flex-grow">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Price: ₱{{ number_format($item['price'], 2) }}</p>
                        </div>
                        
                        <!-- Quantity Controls (FUNCTIONAL) -->
                        <div class="mx-4 flex items-center justify-center space-x-1">
                            <form action="{{ route('cart.update') }}" method="POST" class="flex items-center">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $id }}">
                                
                                <!-- Decrement Button -->
                                <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" 
                                    class="p-1 border border-gray-300 dark:border-gray-600 rounded-l-lg dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 font-bold"
                                    title="Decrease Quantity / Remove if zero"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                </button>

                                <!-- Static Quantity Display -->
                                <input type="number" value="{{ $item['quantity'] }}" min="1" disabled
                                    class="w-12 text-center border-y border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm p-0 m-0"
                                >
                                
                                <!-- Increment Button -->
                                <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" 
                                    class="p-1 border border-gray-300 dark:border-gray-600 rounded-r-lg dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 font-bold"
                                    title="Increase Quantity"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                                
                            </form>
                        </div>
                        
                        <!-- Subtotal -->
                        <div class="text-right ml-4 w-20">
                            <span class="text-xl font-bold text-primary">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                        
                        <!-- Remove Button -->
                        <form action="{{ route('cart.remove') }}" method="POST" class="ml-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $id }}">
                            <button type="submit" class="text-red-500 hover:text-red-700 transition duration-150 p-2 rounded-full hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" title="Remove Item">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3"></path></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary (1/3 width) -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl h-fit border border-gray-100 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 border-b dark:border-gray-700 pb-2">Order Summary</h2>
                
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-semibold">₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-b dark:border-gray-700 pb-3">
                        <span>Shipping:</span>
                        <span class="font-semibold">₱{{ number_format($shipping, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xl font-extrabold text-gray-900 dark:text-white pt-3">
                        <span>Order Total:</span>
                        <span class="text-primary">₱{{ number_format($total, 2) }}</span>
                    </div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Taxes calculated upon final processing.</p>
                
                <!-- Checkout Button -->
                <a href="{{ route('checkout.showForm') }}"
                    class="w-full mt-6 inline-block text-center py-3 px-4 rounded-lg shadow-lg text-lg font-bold text-white bg-primary hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150 transform hover:scale-[1.01]"
                >
                    Proceed to Shipping
                </a>

                <a href="{{ route('shop') }}" class="mt-4 block text-center text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition duration-150">
                    &larr; Continue Shopping
                </a>
            </div>
        </div>

    @else
        <!-- Empty Cart State -->
        <div class="text-center p-16 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-300 dark:border-gray-700">
            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Your Cart is Empty</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Time to fill it with some great products!</p>
            <a href="{{ route('shop') }}" class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-primary hover:bg-green-700 transition duration-150">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection
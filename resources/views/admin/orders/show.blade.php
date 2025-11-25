@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700">
        
        <div class="flex justify-between items-center border-b dark:border-gray-700 pb-4 mb-6">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                Order Details: <span class="text-primary">{{ $order->order_number }}</span>
            </h1>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-primary transition duration-150 flex items-center">
                &larr; Back to Order List
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Order Status & Date -->
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                <span class="text-2xl font-bold 
                    @if($order->status === 'processing' || $order->status === 'pending') text-yellow-600 dark:text-yellow-400
                    @elseif($order->status === 'shipped') text-blue-600 dark:text-blue-400
                    @elseif($order->status === 'completed') text-green-600 dark:text-green-400
                    @else text-red-600 dark:text-red-400
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Date: {{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            
            <!-- Customer Info -->
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $order->user->name ?? 'Guest' }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->user->email ?? 'N/A' }}</p>
            </div>

            <!-- Totals -->
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Total</p>
                <p class="text-2xl font-bold text-primary">₱{{ number_format($order->total_amount, 2) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">ID: {{ $order->id }}</p>
            </div>
        </div>

        <!-- Shipping and Billing Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Shipping Address</h3>
                <address class="text-gray-700 dark:text-gray-300 not-italic space-y-1">
                    <p>{{ $order->shipping_address }}</p>
                </address>
            </div>
            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Billing Address</h3>
                <address class="text-gray-700 dark:text-gray-300 not-italic space-y-1">
                    <p>{{ $order->billing_address }}</p>
                </address>
            </div>
        </div>

        <!-- Ordered Products List -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 border-b dark:border-gray-700 pb-2">
            Products Purchased
        </h2>

        <div class="space-y-4">
            @forelse ($order->items as $item)
                <div class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg dark:bg-gray-700/30">
                    <!-- Product Image -->
                    <img src="{{ asset('storage/' . ($item->product->image_url ?? '')) }}" 
                         alt="{{ $item->product->name ?? 'Product Removed' }}" 
                         class="w-16 h-16 object-cover rounded-md mr-4">

                    <!-- Product Details -->
                    <div class="flex-grow">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item->product->name ?? 'Product Not Found' }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item->product->category ?? '' }}</p>
                    </div>

                    <!-- Quantity & Price -->
                    <div class="text-right flex-shrink-0">
                        <p class="text-md font-medium text-gray-900 dark:text-white">Qty: {{ $item->quantity }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Unit Price: ₱{{ number_format($item->price_at_purchase, 2) }}</p>
                        <p class="text-md font-bold text-primary">Line Total: ₱{{ number_format($item->price_at_purchase * $item->quantity, 2) }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">No items found for this order.</p>
            @endforelse
        </div>

    </div>
</div>
@endsection
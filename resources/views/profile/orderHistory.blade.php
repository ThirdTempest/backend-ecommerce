@extends('layouts.app')

@section('title', 'Order History')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700">
        
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-6 border-b dark:border-gray-700 pb-2">
            My Order History
        </h1>
        <a href="{{ route('profile') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-primary transition duration-150 mb-6 inline-block">
            &larr; Back to Dashboard
        </a>
        
        @if (session('error'))
            <div class="bg-red-100 dark:bg-red-900/50 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-6 rounded-lg" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @elseif (session('success'))
            <div class="bg-green-100 dark:bg-green-900/50 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-6 rounded-lg" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif


        @forelse ($orders as $order)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-4 mb-4 bg-gray-50 dark:bg-gray-700/50">
                <div class="flex justify-between items-start pb-2 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">Order #{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Placed on: {{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right flex items-center space-x-3">
                        
                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full 
                            @if(in_array($order->status, ['processing', 'pending'])) bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-300
                            @elseif($order->status === 'shipped') bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-300
                            @elseif($order->status === 'completed') bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-300
                            @elseif($order->status === 'cancelled') bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-300
                            @else bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-300
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                        
                        <!-- Cancellation Button (NEW) -->
                        @if (!in_array($order->status, ['shipped', 'completed', 'cancelled']))
                            <form action="{{ route('order.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel Order #{{ $order->order_number }}? This action cannot be undone.');">
                                @csrf
                                <button type="submit" 
                                    class="text-xs px-3 py-1 font-semibold rounded-full bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/50 dark:text-red-400 transition duration-150"
                                    title="Cancel Order"
                                >
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="pt-3">
                    <p class="text-2xl font-bold text-primary mt-1 text-right mb-3">Total: ₱{{ number_format($order->total_amount, 2) }}</p>

                    <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Items Included:</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @foreach ($order->items as $item)
                            <li>
                                {{ $item->quantity }}x {{ $item->product->name ?? 'Deleted Product' }} (₱{{ number_format($item->price_at_purchase, 2) }} each)
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <div class="text-center p-10 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <p class="text-xl font-semibold text-gray-700 dark:text-gray-300">You haven't placed any orders yet.</p>
                <a href="{{ route('shop') }}" class="mt-4 inline-block text-primary hover:underline">Start shopping now!</a>
            </div>
        @endforelse

    </div>
</div>
@endsection
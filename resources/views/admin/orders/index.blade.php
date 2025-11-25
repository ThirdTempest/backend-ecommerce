@extends('layouts.app')

@section('title', 'Admin Order Management')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">
            Order Management
        </h1>
        <div class="space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-primary transition duration-150 flex items-center">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 dark:bg-green-900/50 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-300 p-4 mb-6 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden dark:shadow-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">Status / Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!-- NEW LINK TO ORDER DETAIL PAGE -->
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ $order->user->name ?? 'Guest' }} <br>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $order->user->email ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="flex items-center">
                                    @csrf
                                    @method('PUT')
                                    
                                    <!-- AUTO-SUBMIT ON CHANGE -->
                                    <select name="status" id="status-{{ $order->id }}" 
                                        onchange="this.form.submit()"
                                        class="py-1 px-2 border rounded-lg text-sm focus:ring-primary focus:border-primary w-full max-w-[150px]
                                            @if($order->status === 'pending' || $order->status === 'processing') border-yellow-400 bg-yellow-50 dark:bg-yellow-900/50 dark:text-yellow-300
                                            @elseif($order->status === 'shipped') border-blue-400 bg-blue-50 dark:bg-blue-900/50 dark:text-blue-300
                                            @elseif($order->status === 'completed') border-green-400 bg-green-50 dark:bg-green-900/50 dark:text-green-300
                                            @else border-red-400 bg-red-50 dark:bg-red-900/50 dark:text-red-300
                                            @endif
                                            dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                    >
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    
                                    <!-- REMOVED UPDATE BUTTON -->
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700">
            {{ $orders->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
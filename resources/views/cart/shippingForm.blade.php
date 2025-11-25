@extends('layouts.app')

@section('title', 'Shipping Information')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-8 text-center">
        Shipping & Payment
    </h1>

    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Shipping Form (2/3 width) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b dark:border-gray-700 pb-2">
                Delivery Information
            </h2>

            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-500 text-red-700 dark:text-red-300 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Validation Error!</strong>
                    <span class="block sm:inline">Please correct the following fields.</span>
                    <!-- Display specific validation errors to help debugging -->
                    <ul class="list-disc ml-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form submits to the PayMongo initiation route -->
            <form action="{{ route('checkout.initiate') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Billing & Contact Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                        >
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', Auth::user()->email) }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                            {{-- REMOVED disabled attribute --}}
                        >
                        {{-- Kept hidden field just in case, though the visible field now submits the value --}}
                        <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', '09171234567') }}" placeholder="e.g., 09171234567" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('phone') border-red-500 @enderror"
                        >
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Address Info -->
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 pt-4 border-t dark:border-gray-700">Shipping Address</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="line1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Street Address</label>
                        <input id="line1" name="line1" type="text" value="{{ old('line1', 'Sample Street, Block 1') }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('line1') border-red-500 @enderror"
                        >
                        @error('line1') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City/Province</label>
                        <input id="city" name="city" type="text" value="{{ old('city', 'Quezon City') }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('city') border-red-500 @enderror"
                        >
                        @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                        <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', '1100') }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white @error('postal_code') border-red-500 @enderror"
                        >
                        @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Final Submit Button -->
                <div class="pt-6">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-lg shadow-lg text-lg font-bold text-white bg-primary hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150 transform hover:scale-[1.01]"
                    >
                        Proceed
                    </button>
                </div>
            </form>
            
            <!-- Removed: Testing Link for Failure -->
        </div>

        <!-- Order Summary (1/3 width) -->
        <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 p-6 rounded-xl shadow-inner h-fit border border-gray-200 dark:border-gray-600">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 border-b dark:border-gray-600 pb-2">Order Summary</h2>
            
            <div class="space-y-3 text-gray-700 dark:text-gray-300">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-semibold">₱{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between border-b dark:border-gray-600 pb-3">
                    <span>Shipping:</span>
                    <span class="font-semibold">₱{{ number_format($shipping, 2) }}</span>
                </div>
                <div class="flex justify-between text-xl font-extrabold text-gray-900 dark:text-white pt-3">
                    <span>Order Total:</span>
                    <span class="text-primary">₱{{ number_format($total, 2) }}</span>
                </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">You will be redirected to the secure PayMongo payment page to finalize the transaction.</p>
            
            <!-- Highlighted: Edit Cart Items Link -->
            <a href="{{ route('cart.view') }}" class="mt-4 block text-center text-sm font-medium text-primary hover:underline transition duration-150">
                &larr; Edit Cart Items
            </a>
        </div>
    </div>
</div>
@endsection
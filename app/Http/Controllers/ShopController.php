<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Mail; 
use App\Mail\OTPVerification; 
use App\Mail\ContactFormMail; 
use Carbon\Carbon; 
use Illuminate\Support\Facades\DB; // Ensure DB facade is imported for transactions

class ShopController extends Controller
{
    // --- BASIC VIEWS ---
    public function index() 
    { 
        $newArrivals = Product::orderBy('created_at', 'desc')->limit(4)->get();
        return view('home', compact('newArrivals')); 
    }
    
    /**
     * Show the list of all available products, optionally filtered by category or search term.
     */
    public function shopAll(Request $request) 
    { 
        $query = Product::query();
        $currentCategory = null;
        $searchQuery = $request->input('query'); 

        // 1. Handle Category Filter
        if ($request->has('category') && $request->category !== null) {
            $currentCategory = $request->category;
            $query->where('category', $currentCategory);
        }
        
        // 2. Handle Search Query
        if ($searchQuery) {
            // Search product name or description for the keyword
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(16);

        return view('shop', compact('products', 'currentCategory', 'searchQuery')); 
    }

    public function saleProducts()
    {
        $products = Product::whereNotNull('sale_price')->orderBy('price', 'asc')->get();
        return view('sale', compact('products'));
    }

    public function newArrivalsProducts()
    {
        $products = Product::orderBy('created_at', 'desc')->limit(10)->get();
        return view('newArrivals', compact('products'));
    }

    /**
     * Show the contact form page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission and send email.
     */
    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Placeholder: Removed hardcoded email fallback
            $supportEmail = env('MAIL_TO_SUPPORT', 'support@[YOUR_DOMAIN].com'); 
            Mail::to($supportEmail)->send(new ContactFormMail($validated));
            return back()->with('success', 'Thank thank you for your inquiry! We will respond to you within 24 hours.');
        } catch (\Exception $e) {
            \Log::error("Contact Mail Error: " . $e->getMessage());
            return back()->with('error', 'We encountered an issue sending your message. Please try again or email us directly.');
        }
    }
    
    public function termsOfService() { return view('legal.terms'); }
    public function privacyPolicy() { return view('legal.privacy'); }
    public function accessibilityStatement() { return view('legal.accessibility'); }

    // --- AUTH VIEWS & LOGIC ---
    public function login() { return view('login'); }
    public function register() { return view('register'); }
    
    public function storeRegister(Request $request) 
    {
        $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $otp = rand(100000, 999999);
        $user = User::create([
            'name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password),
            'is_admin' => false, 'otp_code' => $otp, 'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);
        try { Mail::to($user->email)->send(new OTPVerification($otp)); } catch (\Exception $e) { \Log::error("Mail Error: " . $e->getMessage()); }
        return redirect()->route('otp.verify')->with('email', $user->email);
    }
    public function showOtpForm()
    {
        if (!session('email')) { return redirect()->route('login'); }
        return view('auth.verify-otp');
    }
    public function verifyOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email', 'otp' => 'required|string|digits:6', ]);
        $user = User::where('email', $request->email)->first();
        $inputOtp = trim((string)$request->otp);
        if (!$user || $user->otp_code != $inputOtp) {
            return back()->withErrors(['otp' => 'Invalid verification code.'])->withInput(['email' => $request->email]);
        }
        if (Carbon::parse($user->otp_expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'Verification code has expired. Please register again.'])->withInput(['email' => $request->email]);
        }
        $user->update(['otp_code' => null, 'otp_expires_at' => null, 'email_verified_at' => now(), ]);
        Auth::login($user);
        return redirect()->route('profile')->with('success', 'Account verified successfully!');
    }
    public function storeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email', 'password' => 'required',
        ]);
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user->is_admin) { return redirect()->route('admin.dashboard'); }
            return redirect()->route('profile');
        }
        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'You have been logged out.');
    }
    public function profile() 
    { 
        $recentOrders = Auth::user()->orders()->orderBy('created_at', 'desc')->limit(3)->get();
        return view('profile', compact('recentOrders')); 
    }
    public function orderHistory() 
    {
        $orders = Auth::user()->orders()->with('items')->orderBy('created_at', 'desc')->get();
        return view('profile.orderHistory', compact('orders'));
    }
    public function savedAddresses()
    {
        $mockAddresses = [
            (object)['id' => 1, 'type' => 'Default Shipping', 'address' => 'Sample Street, Quezon City, 1100'],
            (object)['id' => 2, 'type' => 'Office', 'address' => 'Unit 4B, Makati Ave, Makati City, 1226'],
        ];
        return view('profile.savedAddresses', ['addresses' => $mockAddresses]);
    }

    // --- CART & CHECKOUT MANAGEMENT ---
    public function addToCart(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $productId = $request->product_id;
        $product = Product::find($productId);
        $quantity = 1;
        $cart = Session::get('cart', []);
        
        // --- STOCK CHECK FOR ADD TO CART (NEW) ---
        // 1. Check if product is available at all
        if ($product->stock === 0) {
            return back()->with('error', "Sorry, {$product->name} is currently out of stock. Please check back later.");
        }
        
        // 2. Check if new quantity exceeds stock
        $currentCartQuantity = $cart[$productId]['quantity'] ?? 0;
        $newTotalQuantity = $currentCartQuantity + $quantity;

        if ($product->stock < $newTotalQuantity) {
            return back()->with('error', "Sorry, only {$product->stock} unit(s) of {$product->name} are available in stock. You currently have {$currentCartQuantity} in your cart.");
        }
        // --- END STOCK CHECK ---

        $currentPrice = $product->sale_price ?? $product->price;
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $newTotalQuantity;
        } else {
            $cart[$productId] = [
                "id" => $productId, "name" => $product->name, "quantity" => $quantity, "price" => $currentPrice, 
                "original_price" => $product->price, "image_url" => $product->image_url
            ];
        }
        Session::put('cart', $cart);
        return redirect()->route('cart.view')->with('success', $product->name . ' added to cart!');
    }
    
    /**
     * Handle updating product quantity in the session cart.
     */
    public function updateCartQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer', 
        ]);

        $productId = $request->product_id;
        $newQuantity = $request->quantity;
        $productName = '';
        $product = Product::find($productId); // Get product data for stock check

        if (Session::has('cart')) {
            $cart = Session::get('cart');
            
            if (isset($cart[$productId])) {
                $productName = $cart[$productId]['name'];
                
                // --- STOCK CHECK FOR UPDATE (NEW) ---
                if ($product && $newQuantity > $product->stock) {
                    return back()->with('error', "Cannot update quantity. Only {$product->stock} unit(s) of {$productName} are in stock.");
                }
                // --- END STOCK CHECK ---
                
                if ($newQuantity <= 0) {
                    unset($cart[$productId]);
                    $message = $productName . ' was removed from your cart.';
                } else {
                    $cart[$productId]['quantity'] = $newQuantity;
                    $message = $productName . ' quantity updated to ' . $newQuantity . '.';
                }
                
                Session::put('cart', $cart);
                return redirect()->route('cart.view')->with('success', $message);
            }
        }
        
        return redirect()->route('cart.view')->with('error', 'Item not found in cart.');
    }


    public function removeFromCart(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $productId = $request->product_id;
        $productName = '';

        if (Session::has('cart')) {
            $cart = Session::get('cart');
            if (isset($cart[$productId])) {
                $productName = $cart[$productId]['name'];
                unset($cart[$productId]);
                Session::put('cart', $cart);
                return redirect()->route('cart.view')->with('success', $productName . ' was removed from your cart.');
            }
        }
        return redirect()->route('cart.view')->with('error', 'Item not found in cart.');
    }
    
    // --- ORDER CANCELLATION (NEW) ---

    public function cancelOrder(Order $order)
    {
        // 1. Authorization check (ensure current user owns the order)
        if ($order->user_id !== Auth::id()) {
            return back()->with('error', 'You are not authorized to cancel this order.');
        }

        // 2. State check (cancellation allowed only before shipping/completion)
        if (in_array($order->status, ['shipped', 'completed', 'cancelled'])) {
            return back()->with('error', 'This order is already ' . $order->status . ' and cannot be cancelled.');
        }
        
        // 3. Perform Transaction (Update status and refund stock)
        try {
            DB::transaction(function () use ($order) {
                // Set order status to cancelled
                $order->update(['status' => 'cancelled']);

                // Refund stock back to inventory
                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            });
            
            return redirect()->route('profile.orders')->with('success', "Order #{$order->order_number} has been successfully cancelled and stock has been refunded.");
            
        } catch (\Exception $e) {
            \Log::error("Order Cancellation Error: " . $e->getMessage());
            return back()->with('error', 'A database error occurred during cancellation. Please try again.');
        }
    }
    
    /**
     * Show the cart page and calculate totals (UPDATED SHIPPING LOGIC).
     */
    public function viewCart()
    {
        if (!Auth::check()) { return redirect()->route('login')->with('error', 'Please log in to view your cart and checkout.'); }
        $cart = Session::get('cart', []);
        
        // 1. Calculate Subtotal
        $subtotal = array_sum(array_map(function ($item) { return $item['price'] * $item['quantity']; }, $cart));
        
        // 2. Calculate Shipping (20% of Subtotal, rounded)
        $shipping = round($subtotal * 0.20, 2); 
        
        // 3. Calculate Total
        $total = $subtotal + $shipping; 
        $totalInCentavos = intval(round($total * 100));

        return view('cart.viewCart', compact('cart', 'subtotal', 'shipping', 'total', 'totalInCentavos'));
    }

    public function showShippingForm()
    {
        if (!Auth::check() || empty(Session::get('cart'))) { return redirect()->route('shop')->with('error', 'Your cart is empty or you are not logged in.'); }
        $cart = Session::get('cart', []);
        $subtotal = array_sum(array_map(function ($item) { return $item['price'] * $item['quantity']; }, $cart));
        
        // Shipping is 20% of subtotal for display on this page too
        $shipping = round($subtotal * 0.20, 2); 
        
        $total = $subtotal + $shipping;
        return view('cart.shippingForm', compact('subtotal', 'shipping', 'total'));
    }
    
    /**
     * Initiates the payment process (ACTUAL PAYMONGO API CALL).
     */
    public function initiatePayment(Request $request)
    {
        if (!Auth::check() || !Session::has('cart') || empty(Session::get('cart'))) { 
            return redirect()->route('shop')->with('error', 'Your cart is empty or you are not logged in.'); 
        }
        
        // FIX: Stricter Phone validation to prevent API rejection
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'email' => 'required|email|max:255', 
            'phone' => ['required', 'string', 'max:15', 'regex:/^(09|\+639)\d{9}$/'], // PH format
            'line1' => 'required|string|max:255', 
            'city' => 'required|string|max:100', 
            'postal_code' => 'required|string|max:10', 
        ]);
        
        $cart = Session::get('cart'); 
        $user = Auth::user(); 
        
        // Recalculate totals needed for API and session storage
        $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $shipping = round($subtotal * 0.20, 2);
        $totalAmount = $subtotal + $shipping;
        
        // Convert to centavos (required by PayMongo)
        $totalInCentavos = intval(round($totalAmount * 100));

        // --- PAYMONGO PAYLOAD CONSTRUCTION ---
        $lineItems = [];
        foreach ($cart as $item) { 
            $lineItems[] = [ 
                'currency' => 'PHP', 
                'amount' => intval(round($item['price'] * 100)), // Unit Price
                'name' => $item['name'], 
                'quantity' => (int)$item['quantity'], // Cast to int to prevent API error
            ]; 
        }
        
        // Add shipping fee as a line item
        $lineItems[] = [
            'currency' => 'PHP',
            'amount' => intval(round($shipping * 100)),
            'name' => 'Shipping Fee',
            'quantity' => 1,
        ];

        $secretKey = env('PAYMONGO_SECRET_KEY'); 
        $successUrl = route('checkout.success'); 
        $failureUrl = route('checkout.failure');

        $payload = [ 
            'data' => [ 
                'attributes' => [ 
                    'billing' => [ 
                        'name' => $validated['name'], 
                        'email' => $validated['email'], 
                        'phone' => $validated['phone'], 
                        'address' => [ 
                            'line1' => $validated['line1'], 
                            'city' => $validated['city'], 
                            'country' => 'PH', 
                            'postal_code' => $validated['postal_code']
                        ] 
                    ], 
                    'send_email' => true, 
                    'currency' => 'PHP',
                    'amount' => $totalInCentavos, 
                    'payment_method_types' => ['gcash', 'paymaya', 'card'], 
                    'line_items' => $lineItems, 
                    'success_url' => $successUrl, 
                    'cancel_url' => $failureUrl, 
                    'description' => 'E-SHOP Purchase ' . date('Y-m-d'), 
                ], 
            ], 
        ]; 
        
        // Store pending order details in session before redirect
        Session::put('pending_order_data', [ 
            'total_amount' => $totalAmount, 
            'cart_items' => $cart, 
            'user_id' => $user->id, 
            'shipping_address' => implode(', ', [$validated['line1'], $validated['city'], $validated['postal_code']]), 
            'billing_address' => implode(', ', [$validated['line1'], $validated['city'], $validated['postal_code']]), 
        ]);

        // --- REAL PAYMONGO API CALL ---
        try {
            // FIX: Ignoring SSL verification for local dev (XAMPP/WSL/local machine issues)
            $response = Http::withBasicAuth($secretKey, '')
                ->withOptions(['verify' => false]) 
                ->post('https://api.paymongo.com/v1/checkout_sessions', $payload);
            
            if ($response->successful() && $response->json('data.attributes.checkout_url')) {
                $checkoutUrl = $response->json('data.attributes.checkout_url');
                return redirect()->away($checkoutUrl);
            }
            
            // If API call fails, provide detailed error
            $apiError = $response->json('errors.0.detail') ?? 'Unknown API error.';
            \Log::error("PayMongo API Error: " . $response->body());
            
            // Return specific API rejection message
            return back()->withErrors(['api' => 'Payment processor error: ' . $apiError])
                        ->withInput();

        } catch (\Exception $e) {
            \Log::error("PayMongo Connection Error: " . $e->getMessage());
            
            // Return general connection error message
            return back()->withErrors(['api' => 'Could not connect to payment gateway. Please check your network and PHP environment (CURL/SSL).'])
                        ->withInput();
        }
    }
    
    // ... (checkoutSuccess and checkoutFailure methods remain unchanged) ...
    public function checkoutSuccess(Request $request)
    {
        $orderData = Session::pull('pending_order_data');
        if (!$orderData) { return redirect()->route('profile')->with('error', 'Checkout session expired or data missing.'); }
        
        $totalAmount = $orderData['total_amount']; 
        
        $order = Order::create([ 
            'user_id' => $orderData['user_id'], 
            'order_number' => 'ESHOP-' . time() . Str::random(4), 
            'total_amount' => $totalAmount, 
            'shipping_address' => $orderData['shipping_address'], 
            'billing_address' => $orderData['billing_address'], 
            'status' => 'processing', 
        ]);
        
        // --- INVENTORY FIX: DECREMENT STOCK ---
        foreach ($orderData['cart_items'] as $productId => $item) {
            // Find the product and decrement stock by the quantity purchased
            Product::where('id', $productId)->decrement('stock', $item['quantity']); 

             OrderDetail::create([ 
                 'order_id' => $order->id, 
                 'product_id' => $item['id'], 
                 'quantity' => $item['quantity'], 
                 'price_at_purchase' => $item['price'], 
            ]);
        }
        Session::forget('cart');
        return view('cart.success')->with(['mock_order_number' => $order->order_number]);
    }
    public function checkoutFailure()
    {
        Session::forget('pending_order_data'); 
        return view('cart.failure');
    }
}
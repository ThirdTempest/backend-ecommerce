<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerification;

class AuthController extends Controller
{
    // Handle Registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otp = rand(100000, 999999);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'otp_code' => $otp,
        ]);

        try {
            \Log::info("DEV OTP for {$user->email}: {$otp}");
            Mail::to($user->email)->send(new OtpVerification($otp));
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP: " . $e->getMessage());
        }

        // Return success but NO TOKEN yet. User must verify first.
        return response()->json([
            'message' => 'Registration successful. Please check your email for OTP.',
            'require_otp' => true,
            'email' => $user->email
        ], 200);
    }

    // Handle Login with 2FA
    public function login(Request $request)
    {
        // 1. Check Password
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Check 2FA Type
        $type = $user->two_factor_type ?? 'email'; // Default to email

        // Allow overriding preference to force Email OTP (e.g. from Credentials tab)
        if ($request->boolean('force_email')) {
            $type = 'email';
        }

        // Determine available methods
        $availableMethods = ['email'];
        if ($user->two_factor_secret) {
            $availableMethods[] = 'totp';
        }
        if ($user->face_descriptor) {
            $availableMethods[] = 'face';
        }

        if ($type === 'totp') {
            return response()->json([
                'message' => 'Please enter your TOTP code.',
                'require_otp' => true,
                'otp_type' => 'totp',
                'available_methods' => $availableMethods,
                'email' => $user->email
            ]);
        } elseif ($type === 'face') {
             return response()->json([
                'message' => 'Please scan your face.',
                'require_otp' => true,
                'otp_type' => 'face',
                'available_methods' => $availableMethods,
                'email' => $user->email
            ]);
        }

        // Default: Email OTP
        // 2. Generate New OTP for Login 2FA
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->save();

        // 3. Send OTP Email
        \Log::info("DEV OTP for {$user->email}: {$otp}");
        try {
            Mail::to($user->email)->send(new OtpVerification($otp));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP email.'], 500);
        }

        // 4. Return response telling frontend to redirect to Verify Page
        // We DO NOT return the token here anymore.
        return response()->json([
            'message' => 'OTP sent to your email.',
            'require_otp' => true,
            'otp_type' => 'email',
            'available_methods' => $availableMethods,
            'email' => $user->email,
            'dev_otp' => config('app.debug') ? $otp : null // Only return in debug mode
        ]);
    }

    // Handle Logout
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Logged out successfully']);
    }
}

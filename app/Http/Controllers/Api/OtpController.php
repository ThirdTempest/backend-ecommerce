<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerification;

class OtpController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP code.'], 400);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->otp_code = null;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Account verified successfully!',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Resend Function with Spam Protection
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // FIX: Check if the last update was less than 60 seconds ago
        // updated_at changes whenever we save() the user, which happens when generating OTP
        if ($user->updated_at && now()->diffInSeconds($user->updated_at) < 60) {
            return response()->json([
                'message' => 'Please wait 1 minute before requesting a new code.'
            ], 429); // 429 = Too Many Requests
        }

        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->save(); // This updates 'updated_at' timestamp

        try {
            Mail::to($user->email)->queue(new OtpVerification($otp));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email failed: ' . $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'OTP resent successfully']);
    }
}

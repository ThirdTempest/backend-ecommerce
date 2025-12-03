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
        \Log::info('OTP Verify Request:', $request->all());
        
        $request->validate([
            'email' => 'required|email',
            'otp' => 'nullable|string',
            'descriptor' => 'nullable|string',
            'type' => 'nullable|string|in:email,totp,face'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $type = $request->type ?? 'email';

        if ($type === 'email') {
            if ($user->otp_code !== $request->otp) {
                return response()->json(['message' => 'Invalid OTP code.'], 400);
            }
            // Clear OTP after use
             $user->otp_code = null;
        } elseif ($type === 'totp') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp, 2);
            if (!$valid) {
                return response()->json(['message' => 'Invalid TOTP code.'], 400);
            }
        } elseif ($type === 'face') {
            if (!$request->descriptor || !$user->face_descriptor) {
                 return response()->json(['message' => 'Face data missing.'], 400);
            }
            
            $stored = json_decode($user->face_descriptor);
            $incoming = json_decode($request->descriptor);
            
            if (!$stored || !$incoming) {
                 return response()->json(['message' => 'Invalid face data.'], 400);
            }

            $distance = $this->euclideanDistance($stored, $incoming);
            if ($distance > 0.5) { // Threshold
                 return response()->json(['message' => 'Face not recognized.', 'distance' => $distance], 401);
            }
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Verified successfully!',
            'token' => $token,
            'user' => $user
        ]);
    }

    private function euclideanDistance($a, $b)
    {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }
        return sqrt($sum);
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

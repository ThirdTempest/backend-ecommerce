<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;

class QrLoginController extends Controller
{
    // 1. Generate a new QR Session
    public function generate()
    {
        $token = Str::random(40);
        
        // Store in cache for 2 minutes
        Cache::put("qr_login_{$token}", ['status' => 'pending'], 120);

        return response()->json([
            'token' => $token,
            'url' => $token // In a real app, this might be a deep link or just the token
        ]);
    }

    // 2. Check status (Polled by Desktop)
    public function check(Request $request)
    {
        $request->validate(['token' => 'required']);
        $token = $request->token;
        
        $data = Cache::get("qr_login_{$token}");

        if (!$data) {
            return response()->json(['status' => 'expired'], 404);
        }

        if ($data['status'] === 'pending') {
            return response()->json(['status' => 'pending']);
        }

        if ($data['status'] === 'approved') {
            // Login the user and generate token
            $user = User::find($data['user_id']);
            
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            // Generate Sanctum token
            $authToken = $user->createToken('auth_token')->plainTextToken;

            // Clear cache
            Cache::forget("qr_login_{$token}");

            return response()->json([
                'status' => 'approved',
                'token' => $authToken,
                'user' => $user
            ]);
        }

        return response()->json(['status' => 'error'], 500);
    }

    // 3. Authorize (Called by Mobile App)
    public function authorizeSession(Request $request)
    {
        $request->validate(['token' => 'required']);
        $token = $request->token;
        $user = $request->user();

        $data = Cache::get("qr_login_{$token}");

        if (!$data) {
            return response()->json(['message' => 'Invalid or expired QR code.'], 404);
        }

        // Update cache to approved
        Cache::put("qr_login_{$token}", [
            'status' => 'approved',
            'user_id' => $user->id
        ], 120);

        return response()->json(['message' => 'Login authorized successfully.']);
    }
}

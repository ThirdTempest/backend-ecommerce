<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string', // JSON string of 128 floats
        ]);

        $user = $request->user();
        $user->face_descriptor = $request->descriptor;
        $user->save();

        return response()->json(['message' => 'Face registered successfully.']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->face_descriptor) {
            return response()->json(['message' => 'Face not registered.'], 400);
        }

        // Decode descriptors
        $storedDescriptor = json_decode($user->face_descriptor);
        $incomingDescriptor = json_decode($request->descriptor);

        if (!$storedDescriptor || !$incomingDescriptor) {
             return response()->json(['message' => 'Invalid descriptor format.'], 400);
        }

        // Calculate Euclidean distance
        $distance = $this->euclideanDistance($storedDescriptor, $incomingDescriptor);

        // Threshold for face matching (usually around 0.6 for dlib/face-api.js)
        // We can adjust this based on testing.
        $threshold = 0.5;

        if ($distance < $threshold) {
             $user->two_factor_type = 'face';
             $user->save();
             return response()->json(['message' => 'Face verified successfully.']);
        }

        return response()->json(['message' => 'Face not recognized.', 'distance' => $distance], 401);
    }

    private function euclideanDistance($a, $b)
    {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }
        return sqrt($sum);
    }

    public function remove(Request $request)
    {
        $user = $request->user();
        $user->face_descriptor = null;
        if ($user->two_factor_type === 'face') {
            $user->two_factor_type = 'email';
        }
        $user->save();

        return response()->json(['message' => 'Face recognition removed.']);
    }
}

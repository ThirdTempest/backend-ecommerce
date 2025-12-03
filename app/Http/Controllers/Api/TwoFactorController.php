<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return response()->json([
            'secret' => $user->two_factor_secret,
            'qr_code_url' => $qrCodeUrl, // For apps that support URL
            'qr_code_svg' => $qrCodeSvg
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        // Allow for significant time drift (8 windows = 4 minutes)
        // Debugging: Log the secret and code
        \Log::info("Verifying TOTP. Secret: {$user->two_factor_secret}, Code: {$request->code}");
        
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code, 8);
        
        \Log::info("TOTP Result: " . ($valid ? 'Valid' : 'Invalid'));

        if ($valid) {
            $user->two_factor_type = 'totp';
            $user->save();
            return response()->json(['message' => '2FA (TOTP) enabled successfully.']);
        }

        return response()->json(['message' => 'Invalid code.'], 400);
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        $user->two_factor_type = 'email'; // Revert to default
        $user->two_factor_secret = null;
        $user->save();

        return response()->json(['message' => 'TOTP configuration removed. Reverted to Email OTP.']);
    }

    public function setPreference(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,totp,face',
        ]);

        $user = $request->user();
        $type = $request->type;

        // Validation: Can only set if configured
        if ($type === 'totp' && !$user->two_factor_secret) {
            return response()->json(['message' => 'TOTP is not configured.'], 400);
        }

        if ($type === 'face' && !$user->face_descriptor) {
            return response()->json(['message' => 'Face recognition is not configured.'], 400);
        }

        $user->two_factor_type = $type;
        $user->save();

        return response()->json(['message' => "Preferred 2FA method set to {$type}."]);
    }
}

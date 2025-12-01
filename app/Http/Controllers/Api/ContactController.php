<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactInquiry;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        // Send Email to Admin
        // Replace 'admin@eshop.com' with your actual email to test
        Mail::to('admin@eshop.com')->send(new ContactInquiry($validated));

        return response()->json([
            'message' => 'Thank you! Your message has been sent.'
        ], 200);
    }
}

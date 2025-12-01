<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        // Dummy Addresses
        return response()->json([
            [
                'id' => 1,
                'type' => 'Home',
                'address' => '123 Main St, Barangay Mabolo, Cebu City, 6000',
            ],
            [
                'id' => 2,
                'type' => 'Office',
                'address' => 'Unit 404, IT Park, Lahug, Cebu City, 6000',
            ]
        ]);
    }
}

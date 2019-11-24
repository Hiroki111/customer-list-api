<?php

namespace App\Http\Controllers;

use App\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Customer::all(),
        ], 201);
    }
}

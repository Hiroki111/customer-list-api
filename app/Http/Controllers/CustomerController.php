<?php

namespace App\Http\Controllers;

use App\Customer;

class CustomerController extends Controller
{
    private $pageSize = 10;

    public function index()
    {
        return response()->json([
            'data' => Customer::take($this->pageSize)->get(),
        ], 201);
    }
}

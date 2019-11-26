<?php

namespace App\Http\Controllers;

use App\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $pageSize = request('pageSize', null);
        $start = request('start', 0);
        $keyword = request('keyword', null);
        return response()->json([
            'data' => Customer::getCustomerList($pageSize, $start, $keyword),
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => Customer::find($id),
        ], 201);
    }
}

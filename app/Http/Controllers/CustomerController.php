<?php

namespace App\Http\Controllers;

use App\Customer;

class CustomerController extends Controller
{
    private $defaultPageSize = 10;

    public function index()
    {
        $pageSize = request('pageSize', $this->defaultPageSize);
        $start = request('start', 0);
        $keyword = request('keyword');
        return response()->json([
            'data' => Customer::where('id', '>=', $start)->where('name', 'LIKE', "%$keyword%")->take($pageSize)->get(),
        ], 201);
    }
}

<?php

namespace App\Http\Controllers;

use App\Customer;
use Validator;

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

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json(['messages' => $validator->errors()], 400);
        }

        Customer::create([
            'name' => request('name'),
            'phone' => request('phone'),
            'email' => request('email'),
            'address' => request('address'),
            'group_id' => request('group_id'),
            'note' => request('note'),
        ]);
        return response()->json([], 201);
    }

    public function update($id)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json(['messages' => $validator->errors()], 400);
        }

        Customer::findOrFail($id)->fill(request()->all())->save();
        return response()->json([], 201);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json([], 201);
    }
}

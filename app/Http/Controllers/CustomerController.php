<?php

namespace App\Http\Controllers;

use App\Customer;
use Validator;

class CustomerController extends Controller
{
    public function index()
    {
        $pageSize = request('pageSize', null);
        $keyword = request('keyword', null);
        return response()->json(Customer::getCustomerList($pageSize, $keyword), 200);
    }

    public function show($id)
    {
        return response()->json([
            'data' => Customer::with('group')->find($id),
        ], 200);
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
        return response()->json([], 200);
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
        return response()->json([], 200);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json([], 200);
    }
}

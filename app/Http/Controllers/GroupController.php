<?php

namespace App\Http\Controllers;

use App\Group;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Group::all()], 200);
    }
}

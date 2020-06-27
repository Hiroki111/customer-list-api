<?php

namespace App\Http\Controllers;

use App\Group;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json(['groups' => Group::all()], 200);
    }
}

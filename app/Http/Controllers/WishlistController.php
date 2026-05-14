<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        return redirect()->route('account.index');
    }

    public function add(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function toggle(Request $request)
    {
        return response()->json(['success' => true]);
    }
}

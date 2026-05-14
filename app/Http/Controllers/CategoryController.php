<?php

namespace App\Http\Controllers;

class CategoryController extends Controller
{
    public function index()
    {
        return redirect()->route('products.index');
    }
}

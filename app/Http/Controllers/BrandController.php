<?php

namespace App\Http\Controllers;

class BrandController extends Controller
{
    public function index()
    {
        return redirect()->route('products.index');
    }
}

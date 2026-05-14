<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return redirect()->route('account.index');
    }

    public function show(Order $order)
    {
        return redirect()->route('account.orders.show', $order);
    }

    public function track()
    {
        return redirect()->route('track.index');
    }
}

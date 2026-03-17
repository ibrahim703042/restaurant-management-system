<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.orders.manage']);
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['client', 'user'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'client', 'user', 'bill']);

        return view('orders.show', compact('order'));
    }
}

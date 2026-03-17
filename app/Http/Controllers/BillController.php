<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.bills.manage']);
    }

    public function index()
    {
        $bills = Bill::query()->with(['order.client'])->orderByDesc('id')->paginate(25);

        return view('bills.index', compact('bills'));
    }

    public function show(Bill $bill)
    {
        $bill->load(['order.items.product', 'order.client', 'payments', 'debts']);

        return view('bills.show', compact('bill'));
    }

    public function printView(Bill $bill)
    {
        $bill->load(['order.items.product', 'order.client']);

        return view('bills.print', compact('bill'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Support\QrCodeSvg;

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
        $verifyUrl = $bill->verifyUrl();
        $qrSvg = QrCodeSvg::forData($verifyUrl, 180, 6);

        return view('bills.show', compact('bill', 'verifyUrl', 'qrSvg'));
    }

    public function printView(Bill $bill)
    {
        $bill->load(['order.items.product', 'order.client']);
        $verifyUrl = $bill->verifyUrl();
        $qrSvg = QrCodeSvg::forData($verifyUrl, 140, 4);

        return view('bills.print', compact('bill', 'verifyUrl', 'qrSvg'));
    }
}

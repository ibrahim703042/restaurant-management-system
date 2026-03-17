<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.payments.manage']);
    }

    public function index()
    {
        $payments = Payment::query()
            ->with(['bill.order', 'client', 'user'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('payments.index', compact('payments'));
    }
}

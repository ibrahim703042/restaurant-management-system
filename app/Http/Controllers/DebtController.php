<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.debts.manage']);
    }

    public function index()
    {
        $debts = Debt::query()
            ->where('status', 'open')
            ->where('balance', '>', 0)
            ->with(['client', 'bill.order'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('debts.index', compact('debts'));
    }

    public function recordPayment(Request $request, Debt $debt)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$debt->balance,
            'payment_method' => 'required|in:cash,mobile_money,bank',
        ]);

        DB::transaction(function () use ($debt, $data) {
            Payment::create([
                'bill_id' => $debt->bill_id,
                'client_id' => $debt->client_id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
            ]);

            $newPaid = round((float) $debt->amount_paid + $data['amount'], 2);
            $newBalance = round((float) $debt->balance - $data['amount'], 2);
            $debt->update([
                'amount_paid' => $newPaid,
                'balance' => max(0, $newBalance),
                'status' => $newBalance <= 0 ? 'settled' : 'open',
            ]);

            $payAmt = min((float) $data['amount'], (float) $debt->balance);
            Client::whereKey($debt->client_id)->decrement('debt_balance', $payAmt);

            if ($newBalance <= 0) {
                $debt->bill->update(['payment_status' => 'paid']);
            }
        });

        return redirect()->route('debts.index')->with('status', 'Debt payment recorded.');
    }
}

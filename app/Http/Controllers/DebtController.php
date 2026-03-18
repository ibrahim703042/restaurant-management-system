<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Client;
use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.debts.manage']);
    }

    public function index()
    {
        return view('debts.index');
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Debt::query()
            ->where('status', 'open')
            ->where('balance', '>', 0)
            ->with(['client', 'bill.order']);

        return $this->dataTablesOf(
            $base,
            $request,
            function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('bill', fn ($b) => $b->where('bill_number', 'like', "%{$search}%"));
                });
            },
            function ($q) use ($request) {
                $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $q->orderBy('id', $dir);
            },
            function (Debt $d) {
                $billUrl = route('bills.show', $d->bill_id);
                $payUrl = route('debts.pay', $d);

                return [
                    'id' => $d->id,
                    'client' => e($d->client->name ?? '—'),
                    'bill' => e($d->bill->bill_number ?? '—'),
                    'balance' => number_format((float) $d->balance, 0),
                    'owed' => number_format((float) $d->amount_owed, 0),
                    'paid' => number_format((float) $d->amount_paid, 0),
                    'actions' => '<div class="btn-group btn-group-sm">'
                        .'<a href="'.e($billUrl).'" class="btn btn-outline-primary" title="Bill"><i class="fas fa-file-invoice"></i></a>'
                        .'<button type="button" class="btn btn-outline-success btn-pay-debt" data-debt-id="'.$d->id.'" data-balance="'.e($d->balance).'" data-client="'.e($d->client->name ?? '').'" data-pay-url="'.e($payUrl).'" title="Pay"><i class="fas fa-money-bill-wave"></i></button>'
                        .'</div>',
                ];
            }
        );
    }

    public function recordPayment(Request $request, Debt $debt)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$debt->balance,
            'payment_method' => 'required|in:cash,mobile_money,bank',
        ]);

        DB::transaction(function () use ($debt, $data) {
            $balanceBefore = (float) $debt->balance;
            $payAmt = min((float) $data['amount'], $balanceBefore);

            Payment::create([
                'bill_id' => $debt->bill_id,
                'client_id' => $debt->client_id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
            ]);

            $newPaid = round((float) $debt->amount_paid + $data['amount'], 2);
            $newBalance = round($balanceBefore - $data['amount'], 2);
            $debt->update([
                'amount_paid' => $newPaid,
                'balance' => max(0, $newBalance),
                'status' => $newBalance <= 0 ? 'settled' : 'open',
            ]);

            Client::whereKey($debt->client_id)->decrement('debt_balance', $payAmt);

            if ($newBalance <= 0) {
                $debt->bill->update(['payment_status' => 'paid']);
            }
        });

        \App\Models\EmployeeActivity::logForAuthUser('debt.payment', 'Recorded debt payment #'.$debt->id, 'Debt', $debt->id);

        return redirect()->route('debts.index')->with('status', 'Debt payment recorded.');
    }
}

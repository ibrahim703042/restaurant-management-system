<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillVerifyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.qr.verify']);
    }

    public function show(string $token)
    {
        $bill = Bill::query()->where('qr_token', $token)->with(['order.items.product', 'order.client', 'payments'])->firstOrFail();

        $clients = Client::orderBy('name')->get();

        return view('bills.verify', compact('bill', 'clients'));
    }

    public function confirm(Request $request, string $token)
    {
        $bill = Bill::query()->where('qr_token', $token)->firstOrFail();

        $alreadyPaid = (float) Payment::query()->where('bill_id', $bill->id)->sum('amount');
        $remaining = max(0, round((float) $bill->total - $alreadyPaid, 2));

        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'new_client_name' => 'nullable|string|max:255',
            'new_client_phone' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01|max:'.$remaining,
            'payment_method' => 'required|in:cash,mobile_money,bank',
        ]);

        $clientId = $data['client_id'] ?? null;
        if (! $clientId && ! empty($data['new_client_name'])) {
            $client = Client::create([
                'name' => $data['new_client_name'],
                'phone' => $data['new_client_phone'] ?? null,
            ]);
            $clientId = $client->id;
        }

        if (! $clientId) {
            return back()->withErrors(['client_id' => 'Select or create a client.']);
        }

        DB::transaction(function () use ($bill, $data, $clientId) {
            Payment::create([
                'bill_id' => $bill->id,
                'client_id' => $clientId,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
            ]);

            $bill->order->update(['client_id' => $clientId]);

            $totalPaid = Payment::query()->where('bill_id', $bill->id)->sum('amount');
            if ($totalPaid >= $bill->total) {
                $bill->update(['payment_status' => 'paid']);
                Debt::query()->where('bill_id', $bill->id)->update(['status' => 'settled', 'balance' => 0]);
            } else {
                $bill->update(['payment_status' => 'partial']);
            }
        });

        return redirect()->route('bills.verify', $token)->with('status', 'Payment recorded.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Debt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.pos.use']);
    }

    public function index()
    {
        $categories = \App\Models\Category::query()
            ->where('status', 1)
            ->with(['products' => fn ($q) => $q->where('status', 1)->orderBy('product_name')])
            ->orderBy('name')
            ->get();

        $clients = Client::orderBy('name')->get();

        return view('pos.index', compact('categories', 'clients'));
    }

    public function checkout(Request $request)
    {
        $request->merge([
            'client_id' => $request->filled('client_id') ? $request->client_id : null,
        ]);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:cash,mobile_money,bank,debt',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        $total = 0;
        $lines = [];
        foreach ($items as $row) {
            $product = Product::findOrFail($row['product_id']);
            $qty = (int) $row['quantity'];
            $line = round((float) $product->price * $qty, 2);
            $total += $line;
            $lines[] = ['product' => $product, 'quantity' => $qty, 'line_total' => $line];
        }
        $total = round($total, 2);
        $paid = round((float) ($validated['amount_paid'] ?? 0), 2);
        if ($paid > $total) {
            $paid = $total;
        }

        $needsClient = $total > $paid || $validated['payment_method'] === 'debt';
        if ($needsClient && empty($validated['client_id'])) {
            return back()->withInput()->withErrors(['client_id' => 'Select a client for partial payment or debt.']);
        }

        $bill = DB::transaction(function () use ($lines, $total, $paid, $validated) {
            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'client_id' => $validated['client_id'] ?? null,
                'user_id' => auth()->id(),
                'status' => 'completed',
                'total' => $total,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['product']->price,
                    'line_total' => $line['line_total'],
                ]);
            }

            $bill = Bill::create([
                'bill_number' => 'BILL-'.now()->format('Ymd').'-'.str_pad((string) (Bill::query()->count() + 1), 4, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'qr_token' => (string) Str::uuid(),
                'total' => $total,
                'payment_status' => 'unpaid',
            ]);

            if ($paid > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'client_id' => $validated['client_id'] ?? null,
                    'user_id' => auth()->id(),
                    'amount' => $paid,
                    'payment_method' => $validated['payment_method'] === 'debt' ? 'cash' : $validated['payment_method'],
                ]);
            }

            if ($paid >= $total) {
                $bill->update(['payment_status' => 'paid']);
            } elseif ($paid > 0) {
                $bill->update(['payment_status' => 'partial']);
                if (! empty($validated['client_id'])) {
                    $balance = round($total - $paid, 2);
                    Debt::create([
                        'client_id' => $validated['client_id'],
                        'bill_id' => $bill->id,
                        'amount_owed' => $total,
                        'amount_paid' => $paid,
                        'balance' => $balance,
                        'status' => 'open',
                    ]);
                    Client::whereKey($validated['client_id'])->increment('debt_balance', $balance);
                }
            } else {
                if (! empty($validated['client_id'])) {
                    Debt::create([
                        'client_id' => $validated['client_id'],
                        'bill_id' => $bill->id,
                        'amount_owed' => $total,
                        'amount_paid' => 0,
                        'balance' => $total,
                        'status' => 'open',
                    ]);
                    Client::whereKey($validated['client_id'])->increment('debt_balance', $total);
                }
            }

            return $bill;
        });

        return redirect()->route('bills.show', $bill)->with('status', 'Sale completed.');
    }
}

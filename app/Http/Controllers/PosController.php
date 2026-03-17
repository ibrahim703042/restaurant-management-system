<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Client;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Unit;
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
        $stores = auth()->user()->accessibleStores();
        $posStoreId = (int) session('pos_store_id');
        $storeIds = $stores->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($posStoreId && ! in_array($posStoreId, $storeIds, true)) {
            $posStoreId = 0;
        }
        if (! $posStoreId && $stores->isNotEmpty()) {
            $posStoreId = (int) $stores->first()->id;
            session(['pos_store_id' => $posStoreId]);
        }

        $categories = Category::query()
            ->where('status', 1)
            ->whereHas('products', fn ($q) => $q->where('status', 1)->whereNull('store_id'))
            ->with(['products' => fn ($q) => $q->where('status', 1)->whereNull('store_id')->orderBy('product_name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        $clients = Client::orderBy('name')->get();

        return view('pos.index', compact('categories', 'clients', 'stores', 'posStoreId'));
    }

    public function setStore(Request $request)
    {
        $request->validate(['store_id' => 'required|exists:stores,id']);
        $sid = (int) $request->store_id;
        $allowed = auth()->user()->accessibleStores()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array($sid, $allowed, true)) {
            return back()->withErrors(['store_id' => 'Store not allowed for your account.']);
        }
        session(['pos_store_id' => $sid]);

        return redirect()->route('pos.index')->with('status', 'POS store updated.');
    }

    public function checkout(Request $request)
    {
        $request->merge([
            'client_id' => $request->filled('client_id') ? $request->client_id : null,
        ]);

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:cash,mobile_money,bank,debt',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        $storeId = (int) $validated['store_id'];
        $allowedStores = auth()->user()->accessibleStores()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array($storeId, $allowedStores, true)) {
            return back()->withInput()->withErrors(['store_id' => 'Store not allowed for your account.']);
        }
        $total = 0;
        $lines = [];
        foreach ($items as $row) {
            $product = Product::query()->whereNull('store_id')->whereKey($row['product_id'])->firstOrFail();
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

        $unitId = Unit::query()->where('code', 'pcs')->value('id') ?? 1;
        foreach ($lines as $line) {
            $stock = InventoryStock::query()->firstOrCreate(
                ['store_id' => $storeId, 'product_id' => $line['product']->id],
                ['quantity' => 0, 'reorder_level' => 0, 'unit_id' => $unitId]
            );
            if ((float) $stock->quantity < $line['quantity']) {
                return back()->withInput()->withErrors([
                    'items' => 'Stock insuffisant pour « '.$line['product']->product_name.' » (disponible : '.rtrim(rtrim(number_format((float) $stock->quantity, 3, '.', ''), '0'), '.').').',
                ]);
            }
        }

        $bill = DB::transaction(function () use ($lines, $total, $paid, $validated, $storeId) {
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
                InventoryStock::query()
                    ->where('store_id', $storeId)
                    ->where('product_id', $line['product']->id)
                    ->decrement('quantity', $line['quantity']);
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

            return $bill;
        });

        $bill->load('order');
        $bill->syncDebtsFromPayments();

        session(['pos_store_id' => $storeId]);

        return redirect()->route('bills.show', $bill)->with('status', 'Sale completed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryStockController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:inventory.stock.view']);
    }

    public function index(Request $request)
    {
        $stores = Store::orderBy('name')->get();
        $units = Unit::orderBy('code')->get();
        $storeId = (int) ($request->get('store_id') ?: $stores->first()?->id);

        $summary = [
            'total_value' => 0.0,
            'total_products' => 0,
            'in_stock' => 0,
            'low_stock' => 0,
            'out_stock' => 0,
        ];

        $stocks = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url(), 'query' => $request->query()]);

        if ($storeId && $stores->where('id', $storeId)->isNotEmpty()) {
            $productIds = Product::query()->whereNull('store_id')->pluck('id');
            $unitId = Unit::query()->where('code', 'pcs')->value('id') ?? 1;

            foreach ($productIds as $pid) {
                InventoryStock::query()->firstOrCreate(
                    ['store_id' => $storeId, 'product_id' => $pid],
                    ['quantity' => 0, 'reorder_level' => 0, 'unit_id' => $unitId]
                );
            }

            $allForStore = InventoryStock::query()
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->with(['product:id,price'])
                ->get();

            foreach ($allForStore as $row) {
                $summary['total_products']++;
                $price = (float) ($row->product->price ?? 0);
                $summary['total_value'] += (float) $row->quantity * $price;
                $q = (float) $row->quantity;
                $r = (float) $row->reorder_level;
                if ($q <= 0) {
                    $summary['out_stock']++;
                } elseif ($r > 0 && $q <= $r) {
                    $summary['low_stock']++;
                } else {
                    $summary['in_stock']++;
                }
            }

            $base = InventoryStock::query()
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->with(['product.category', 'unit']);

            if ($request->filled('q')) {
                $term = '%'.addcslashes((string) $request->q, '%_\\').'%';
                $base->where(function ($w) use ($term) {
                    $w->whereHas('product', function ($p) use ($term) {
                        $p->where('product_name', 'like', $term)
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term));
                    });
                });
            }

            $status = $request->get('status');
            if ($status === 'out') {
                $base->where('quantity', '<=', 0);
            } elseif ($status === 'low') {
                $base->whereRaw('quantity > 0 AND reorder_level > 0 AND quantity <= reorder_level');
            } elseif ($status === 'in') {
                $base->whereRaw('quantity > 0 AND (reorder_level <= 0 OR quantity > reorder_level)');
            }

            $perPage = (int) $request->get('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $stocks = $base->orderBy('product_id')->paginate($perPage)->withQueryString();
        }

        return view('inventory.index', compact('stores', 'stocks', 'storeId', 'units', 'summary'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'delta' => 'required|numeric',
            'reorder_level' => 'nullable|numeric|min:0',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        DB::transaction(function () use ($data) {
            $row = InventoryStock::query()->firstOrCreate(
                ['store_id' => $data['store_id'], 'product_id' => $data['product_id']],
                [
                    'quantity' => 0,
                    'reorder_level' => 0,
                    'unit_id' => Unit::query()->where('code', 'pcs')->value('id') ?? 1,
                ]
            );
            $row->quantity = max(0, (float) $row->quantity + (float) $data['delta']);
            if ($request->filled('reorder_level')) {
                $row->reorder_level = $data['reorder_level'];
            }
            if ($request->filled('unit_id')) {
                $row->unit_id = $data['unit_id'];
            }
            $row->save();
        });

        $query = array_filter([
            'store_id' => $data['store_id'],
            'q' => $request->get('q'),
            'status' => $request->get('status'),
            'per_page' => $request->get('per_page'),
        ], fn ($v) => $v !== null && $v !== '');

        return redirect()->route('inventory.index', $query)->with('status', __('inventory.stock_updated'));
    }
}

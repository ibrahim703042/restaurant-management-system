<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
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
        $storeId = $request->get('store_id') ?: $stores->first()?->id;
        $stocks = collect();

        if ($storeId) {
            foreach (Product::query()->where('store_id', $storeId)->pluck('id') as $pid) {
                InventoryStock::query()->firstOrCreate(
                    ['store_id' => $storeId, 'product_id' => $pid],
                    ['quantity' => 0, 'reorder_level' => 0]
                );
            }
            $stocks = InventoryStock::query()
                ->where('store_id', $storeId)
                ->with('product.category')
                ->orderBy('product_id')
                ->get();
        }

        return view('inventory.index', compact('stores', 'stocks', 'storeId'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'delta' => 'required|numeric',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $row = InventoryStock::query()->firstOrCreate(
                ['store_id' => $data['store_id'], 'product_id' => $data['product_id']],
                ['quantity' => 0, 'reorder_level' => 0]
            );
            $row->quantity = max(0, (float) $row->quantity + (float) $data['delta']);
            if ($request->filled('reorder_level')) {
                $row->reorder_level = $data['reorder_level'];
            }
            $row->save();
        });

        return redirect()->route('inventory.index', ['store_id' => $data['store_id']])->with('status', 'Stock updated.');
    }
}

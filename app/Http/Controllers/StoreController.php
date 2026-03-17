<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function index()
    {
        return view('stores.manage');
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Store::query()
            ->when($request->filled('status') && $request->status !== '', fn ($q) => $q->where('status', $request->status));

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)->orWhere('code', 'like', $s)->orWhere('phone', 'like', $s);
                });
            },
            fn (Builder $q) => $q->orderBy('name'),
            function (Store $row) {
                $primary = $row->is_primary_stock_location ? '<span class="badge bg-info">Yes</span>' : '—';
                $st = (int) $row->status === 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                return [
                    'id' => $row->id,
                    'name' => e($row->name),
                    'code' => e((string) $row->code),
                    'phone' => e((string) $row->phone),
                    'primary_html' => $primary,
                    'status_html' => $st,
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Store $store): JsonResponse
    {
        return response()->json(['store' => $store]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:32',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:64',
                'notes' => 'nullable|string|max:2000',
                'is_primary_stock_location' => 'nullable|boolean',
                'status' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $store = Store::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_primary_stock_location' => $request->boolean('is_primary_stock_location', true),
            'status' => $data['status'],
        ]);

        $unitId = Unit::query()->where('code', 'pcs')->value('id') ?? 1;
        foreach (Product::query()->whereNull('store_id')->pluck('id') as $pid) {
            InventoryStock::query()->firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $pid],
                ['quantity' => 0, 'reorder_level' => 0, 'unit_id' => $unitId]
            );
        }

        return $this->jsonOk($request, ['message' => 'Store created.', 'store' => $store], redirect()->route('stores.index')->with('status', 'Store created.'));
    }

    public function update(Request $request, Store $store): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:32',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:64',
                'notes' => 'nullable|string|max:2000',
                'status' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $store->fill($data);
        $store->is_primary_stock_location = $request->boolean('is_primary_stock_location', false);
        $store->save();

        return $this->jsonOk($request, ['message' => 'Store updated.', 'store' => $store->fresh()], redirect()->route('stores.index')->with('status', 'Store updated.'));
    }

    public function destroy(Request $request, Store $store): JsonResponse|RedirectResponse
    {
        if ($store->products()->exists() || $store->diningTables()->exists()) {
            $msg = 'Store has products or tables.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors(['delete' => $msg]);
        }
        $store->delete();

        return $this->jsonOk($request, ['message' => 'Store removed.'], redirect()->route('stores.index')->with('status', 'Store removed.'));
    }

    private function jsonOk(Request $request, array $json, $redirect)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($json);
        }

        return $redirect;
    }

    private function jsonErr(Request $request, array $errors, int $code): JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $errors], $code);
        }
        throw ValidationException::withMessages($errors);
    }
}

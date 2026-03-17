<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
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
        $q = Store::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = '%'.$request->search.'%';
                $query->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)->orWhere('code', 'like', $s)->orWhere('phone', 'like', $s);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(Store $store): JsonResponse
    {
        return response()->json(['store' => $store]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
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

        return $this->jsonOk($request, ['message' => 'Store created.', 'store' => $store], redirect()->route('stores.index')->with('status', 'Store created.'));
    }

    public function update(Request $request, Store $store): JsonResponse|\Illuminate\Http\RedirectResponse
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

    public function destroy(Request $request, Store $store): JsonResponse|\Illuminate\Http\RedirectResponse
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

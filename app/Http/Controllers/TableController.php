<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TableController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);

        return view('tables.manage', compact('stores'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $q = DiningTable::query()
            ->with('store:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = '%'.$request->search.'%';
                $query->where(function ($q2) use ($s) {
                    $q2->where('table_name', 'like', $s)->orWhere('section', 'like', $s);
                });
            })
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->store_id));

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderBy('sort_order')->orderBy('table_name')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(DiningTable $dining_table): JsonResponse
    {
        $dining_table->load('store:id,name');

        return response()->json(['table' => $dining_table]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|string|max:32',
                'status' => 'required|integer',
                'store_id' => 'required|exists:stores,id',
                'section' => 'nullable|string|max:64',
                'sort_order' => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $table = DiningTable::create([
            'table_name' => $request->name,
            'section' => $request->section,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'capacity' => $request->capacity,
            'status' => $request->status,
            'store_id' => $request->store_id,
        ]);
        $table->load('store:id,name');

        return $this->jsonOk($request, ['message' => 'Table created.', 'table' => $table], redirect()->route('tables.index')->with('status', 'Table created.'));
    }

    public function update(Request $request, DiningTable $dining_table): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|string|max:32',
                'status' => 'required|integer',
                'store_id' => 'required|exists:stores,id',
                'section' => 'nullable|string|max:64',
                'sort_order' => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $dining_table->update([
            'table_name' => $request->name,
            'section' => $request->section,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'capacity' => $request->capacity,
            'status' => $request->status,
            'store_id' => $request->store_id,
        ]);

        return $this->jsonOk($request, ['message' => 'Table updated.', 'table' => $dining_table->fresh()->load('store:id,name')], redirect()->route('tables.index')->with('status', 'Table updated.'));
    }

    public function destroy(Request $request, DiningTable $dining_table): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $dining_table->delete();

        return $this->jsonOk($request, ['message' => 'Table removed.'], redirect()->route('tables.index')->with('status', 'Table removed.'));
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

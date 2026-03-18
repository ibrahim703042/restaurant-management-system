<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\DiningZone;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TableController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $zones = DiningZone::query()
            ->where('status', 1)
            ->with('store:id,name')
            ->orderBy('name')
            ->get();

        return view('tables.manage', compact('stores', 'zones'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = DiningTable::query()
            ->with(['store:id,name', 'zone:id,name'])
            ->when($request->filled('zone_id'), fn ($q) => $q->where('zone_id', $request->zone_id));

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('table_name', 'like', $s)->orWhere('section', 'like', $s);
                });
            },
            fn (Builder $q) => $q->orderBy('sort_order')->orderBy('table_name'),
            function (DiningTable $row) {
                $st = (int) $row->status === 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Off</span>';

                return [
                    'id' => $row->id,
                    'table_name' => e($row->table_name),
                    'store' => e($row->store->name ?? '—'),
                    'zone' => e($row->zone->name ?? '—'),
                    'section' => e((string) $row->section),
                    'capacity' => (int) $row->capacity,
                    'sort_order' => (int) $row->sort_order,
                    'status_html' => $st,
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(DiningTable $dining_table): JsonResponse
    {
        $dining_table->load('store:id,name');

        return response()->json(['table' => $dining_table]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|string|max:32',
                'status' => 'required|integer',
                'store_id' => 'required|exists:stores,id',
                'zone_id' => ['nullable', Rule::exists('dining_zones', 'id')->where('store_id', $request->store_id)],
                'section' => 'nullable|string|max:64',
                'sort_order' => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $table = DiningTable::create([
            'table_name' => $request->name,
            'section' => $request->section,
            'zone_id' => $request->zone_id ?: null,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'capacity' => $request->capacity,
            'status' => $request->status,
            'store_id' => $request->store_id,
        ]);
        $table->load(['store:id,name', 'zone:id,name']);

        return $this->jsonOk($request, ['message' => 'Table created.', 'table' => $table], redirect()->route('tables.index')->with('status', 'Table created.'));
    }

    public function update(Request $request, DiningTable $dining_table): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|string|max:32',
                'status' => 'required|integer',
                'store_id' => 'required|exists:stores,id',
                'zone_id' => ['nullable', Rule::exists('dining_zones', 'id')->where('store_id', $request->store_id)],
                'section' => 'nullable|string|max:64',
                'sort_order' => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $dining_table->update([
            'table_name' => $request->name,
            'section' => $request->section,
            'zone_id' => $request->zone_id ?: null,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'capacity' => $request->capacity,
            'status' => $request->status,
            'store_id' => $request->store_id,
        ]);

        return $this->jsonOk($request, ['message' => 'Table updated.', 'table' => $dining_table->fresh()->load(['store:id,name', 'zone:id,name'])], redirect()->route('tables.index')->with('status', 'Table updated.'));
    }

    public function destroy(Request $request, DiningTable $dining_table): JsonResponse|RedirectResponse
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

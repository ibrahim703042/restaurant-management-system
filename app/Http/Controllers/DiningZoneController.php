<?php

namespace App\Http\Controllers;

use App\Models\DiningZone;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiningZoneController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);

        return view('dining_zones.manage', compact('stores'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = DiningZone::query()
            ->with('store:id,name')
            ->when($request->filled('store_id'), fn ($query) => $query->where('store_id', $request->store_id));

        return $this->dataTablesOf(
            $base,
            $request,
            fn (Builder $q, string $term) => $q->where('name', 'like', '%'.addcslashes($term, '%_\\').'%'),
            fn (Builder $q) => $q->orderBy('sort_order')->orderBy('name'),
            function (DiningZone $row) {
                $st = (int) $row->status === 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Off</span>';

                return [
                    'id' => $row->id,
                    'store' => e($row->store->name ?? '—'),
                    'name' => e($row->name),
                    'sort_order' => (int) $row->sort_order,
                    'status_html' => $st,
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(DiningZone $dining_zone): JsonResponse
    {
        $dining_zone->load('store:id,name');

        return response()->json(['zone' => $dining_zone]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'store_id' => 'required|exists:stores,id',
                'name' => 'required|string|max:128',
                'sort_order' => 'nullable|integer|min:0',
                'status' => 'required|integer|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $zone = DiningZone::create([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => $data['status'],
        ]);
        $zone->load('store:id,name');

        return $this->jsonOk($request, ['message' => 'Zone created.', 'zone' => $zone], redirect()->route('dining-zones.index')->with('status', 'Zone created.'));
    }

    public function update(Request $request, DiningZone $dining_zone): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'store_id' => 'required|exists:stores,id',
                'name' => 'required|string|max:128',
                'sort_order' => 'nullable|integer|min:0',
                'status' => 'required|integer|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $dining_zone->update([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => $data['status'],
        ]);

        return $this->jsonOk($request, ['message' => 'Zone updated.', 'zone' => $dining_zone->fresh()->load('store:id,name')], redirect()->route('dining-zones.index')->with('status', 'Zone updated.'));
    }

    public function destroy(Request $request, DiningZone $dining_zone): JsonResponse|RedirectResponse
    {
        if ($dining_zone->diningTables()->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Zone has tables assigned.', 'errors' => ['zone' => ['Reassign tables first.']]], 422);
            }

            return back()->withErrors(['delete' => 'Zone has tables.']);
        }
        $dining_zone->delete();

        return $this->jsonOk($request, ['message' => 'Zone removed.'], redirect()->route('dining-zones.index')->with('status', 'Zone removed.'));
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

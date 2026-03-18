<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{
    public function index()
    {
        return view('positions.manage');
    }

    public function listJson(Request $request): JsonResponse
    {
        return $this->dataTablesOf(
            Position::query(),
            $request,
            fn (Builder $q, string $term) => $q->where('title', 'like', '%'.addcslashes($term, '%_\\').'%'),
            fn (Builder $q) => $q->orderBy('title'),
            function (Position $row) {
                return [
                    'id' => $row->id,
                    'title' => e($row->title),
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Position $position): JsonResponse
    {
        return response()->json(['position' => $position]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['title' => 'required|string|max:255']);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $position = Position::create($data);

        return $this->jsonOk($request, ['message' => 'Position created.', 'position' => $position], redirect()->route('positions.index')->with('status', 'Position created.'));
    }

    public function update(Request $request, Position $position): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['title' => 'required|string|max:255']);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $position->update($data);

        return $this->jsonOk($request, ['message' => 'Position updated.', 'position' => $position->fresh()], redirect()->route('positions.index')->with('status', 'Position updated.'));
    }

    public function destroy(Request $request, Position $position): JsonResponse|RedirectResponse
    {
        if ($position->employees()->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Position is assigned to employees.', 'errors' => ['title' => ['Reassign employees first.']]], 422);
            }

            return back()->withErrors(['delete' => 'Position has employees.']);
        }
        $position->delete();

        return $this->jsonOk($request, ['message' => 'Position removed.'], redirect()->route('positions.index')->with('status', 'Position removed.'));
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = \App\Models\Position::whereIn('id', $data['ids'])->delete();
        return response()->json(['message' => __('common.bulk_deleted', ['count' => $deleted])]);
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

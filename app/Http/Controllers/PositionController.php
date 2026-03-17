<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\JsonResponse;
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
        $q = Position::query()
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->search.'%'));

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderBy('title')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(Position $position): JsonResponse
    {
        return response()->json(['position' => $position]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate(['title' => 'required|string|max:255']);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $position = Position::create($data);

        return $this->jsonOk($request, ['message' => 'Position created.', 'position' => $position], redirect()->route('positions.index')->with('status', 'Position created.'));
    }

    public function update(Request $request, Position $position): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate(['title' => 'required|string|max:255']);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $position->update($data);

        return $this->jsonOk($request, ['message' => 'Position updated.', 'position' => $position->fresh()], redirect()->route('positions.index')->with('status', 'Position updated.'));
    }

    public function destroy(Request $request, Position $position): JsonResponse|\Illuminate\Http\RedirectResponse
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

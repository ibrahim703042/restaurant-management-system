<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.manage');
    }

    public function listJson(Request $request): JsonResponse
    {
        $q = Category::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
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

    public function showJson(Category $category): JsonResponse
    {
        return response()->json(['category' => $category]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $cat = Category::create($data);

        return $this->jsonOk($request, ['message' => 'Category created.', 'category' => $cat], redirect()->route('categories.index')->with('status', 'Category created.'));
    }

    public function update(Request $request, Category $category): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $category->update($data);

        return $this->jsonOk($request, ['message' => 'Category updated.', 'category' => $category->fresh()], redirect()->route('categories.index')->with('status', 'Category updated.'));
    }

    public function destroy(Request $request, Category $category): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($category->products()->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Category has products.', 'errors' => ['category' => ['Remove products first.']]], 422);
            }

            return back()->withErrors(['delete' => 'Category has products.']);
        }
        $category->delete();

        return $this->jsonOk($request, ['message' => 'Category removed.'], redirect()->route('categories.index')->with('status', 'Category removed.'));
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

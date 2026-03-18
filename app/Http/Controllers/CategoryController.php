<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Media;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $base = Category::query()
            ->when($request->filled('status') && $request->status !== '', fn ($q) => $q->where('status', $request->status));

        return $this->dataTablesOf(
            $base,
            $request,
            fn (Builder $q, string $term) => $q->where('name', 'like', '%'.addcslashes($term, '%_\\').'%'),
            fn (Builder $q) => $q->orderBy('name'),
            function (Category $row) {
                $img = '<img src="'.e(MediaUrl::forCategory($row)).'" width="36" height="36" class="rounded object-fit-cover" alt="">';
                $st = $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                return [
                    'id' => $row->id,
                    'image_html' => $img,
                    'name' => e($row->name),
                    'status_html' => $st,
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Category $category): JsonResponse
    {
        return response()->json(['category' => $category]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $data = ['name' => $request->name, 'status' => $request->status];
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $cat = Category::create($data);
        if (! empty($data['image'] ?? null)) {
            Media::query()->updateOrCreate(
                ['mediable_type' => Category::class, 'mediable_id' => $cat->id, 'collection' => 'image'],
                ['disk' => 'public', 'path' => $data['image'], 'sort_order' => 0],
            );
        }

        return $this->jsonOk($request, ['message' => 'Category created.', 'category' => $cat], redirect()->route('categories.index')->with('status', 'Category created.'));
    }

    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $category->name = $request->name;
        $category->status = $request->status;
        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('categories', 'public');
        }
        $category->save();
        if ($request->hasFile('image')) {
            Media::query()->updateOrCreate(
                ['mediable_type' => Category::class, 'mediable_id' => $category->id, 'collection' => 'image'],
                ['disk' => 'public', 'path' => $category->image, 'sort_order' => 0],
            );
        }

        return $this->jsonOk($request, ['message' => 'Category updated.', 'category' => $category->fresh()], redirect()->route('categories.index')->with('status', 'Category updated.'));
    }

    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
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

    public function destroyBulk(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = \App\Models\Category::whereIn('id', $data['ids'])->delete();
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

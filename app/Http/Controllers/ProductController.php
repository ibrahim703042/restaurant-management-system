<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Media;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('products.manage', compact('categories'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Product::query()
            ->with(['category:id,name'])
            ->whereNull('store_id')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id));

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('product_name', 'like', $s)->orWhere('description', 'like', $s);
                });
            },
            fn (Builder $q) => $q->orderBy('product_name'),
            function (Product $row) {
                $img = '<img src="'.e(MediaUrl::forProduct($row)).'" width="36" height="36" class="rounded object-fit-cover" alt="">';

                return [
                    'id' => $row->id,
                    'image_html' => $img,
                    'name' => e($row->product_name),
                    'category' => e($row->category->name ?? '—'),
                    'price' => number_format((float) $row->price, 0),
                    'status' => $row->status == 1 ? 'Active' : 'Inactive',
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Product $product): JsonResponse
    {
        $product->load('category:id,name');

        return response()->json(['product' => $product]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:5000',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $imagePath = '';
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'category_id' => $request->category_id,
            'store_id' => null,
            'image' => $imagePath ?: 'products/default.png',
            'status' => $request->status,
        ]);
        $this->syncInventoryRowsForProduct($product->id);
        if ($imagePath !== '' && $imagePath !== 'products/default.png') {
            Media::query()->updateOrCreate(
                ['mediable_type' => Product::class, 'mediable_id' => $product->id, 'collection' => 'image'],
                ['disk' => 'public', 'path' => $imagePath, 'sort_order' => 0],
            );
        }
        $product->load('category:id,name');

        return $this->jsonOk($request, ['message' => 'Product created.', 'product' => $product], redirect()->route('products.index')->with('status', 'Product created.'));
    }

    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:5000',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $product->fill([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);
        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }
        $product->save();
        $this->syncInventoryRowsForProduct($product->id);
        if ($request->hasFile('image') && $product->image && $product->image !== 'products/default.png') {
            Media::query()->updateOrCreate(
                ['mediable_type' => Product::class, 'mediable_id' => $product->id, 'collection' => 'image'],
                ['disk' => 'public', 'path' => $product->image, 'sort_order' => 0],
            );
        }
        $product->load('category:id,name');

        return $this->jsonOk($request, ['message' => 'Product updated.', 'product' => $product], redirect()->route('products.index')->with('status', 'Product updated.'));
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        InventoryStock::query()->where('product_id', $product->id)->delete();
        $product->delete();

        return $this->jsonOk($request, ['message' => 'Product removed.'], redirect()->route('products.index')->with('status', 'Product removed.'));
    }

    private function syncInventoryRowsForProduct(int $productId): void
    {
        $unitId = Unit::query()->where('code', 'pcs')->value('id') ?? 1;
        foreach (Store::query()->pluck('id') as $storeId) {
            InventoryStock::query()->firstOrCreate(
                ['store_id' => $storeId, 'product_id' => $productId],
                ['quantity' => 0, 'reorder_level' => 0, 'unit_id' => $unitId]
            );
        }
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

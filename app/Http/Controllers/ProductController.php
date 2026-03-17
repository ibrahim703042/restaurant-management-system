<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $stores = Store::orderBy('name')->get(['id', 'name']);

        return view('products.manage', compact('categories', 'stores'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $q = Product::query()
            ->with(['category:id,name', 'store:id,name'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = '%'.$request->search.'%';
                $query->where(function ($q2) use ($s) {
                    $q2->where('product_name', 'like', $s)->orWhere('description', 'like', $s);
                });
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->store_id));

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderBy('product_name')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(Product $product): JsonResponse
    {
        $product->load(['category:id,name', 'store:id,name']);

        return response()->json(['product' => $product]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:5000',
                'category_id' => 'required|exists:categories,id',
                'store_id' => 'required|exists:stores,id',
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
            'store_id' => $request->store_id,
            'image' => $imagePath ?: 'products/default.png',
            'status' => $request->status,
        ]);
        \App\Models\InventoryStock::query()->firstOrCreate(
            ['store_id' => $product->store_id, 'product_id' => $product->id],
            ['quantity' => 0, 'reorder_level' => 0]
        );
        $product->load(['category:id,name', 'store:id,name']);

        return $this->jsonOk($request, ['message' => 'Product created.', 'product' => $product], redirect()->route('products.index')->with('status', 'Product created.'));
    }

    public function update(Request $request, Product $product): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:5000',
                'category_id' => 'required|exists:categories,id',
                'store_id' => 'required|exists:stores,id',
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
            'store_id' => $request->store_id,
            'status' => $request->status,
        ]);
        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }
        $product->save();
        $product->load(['category:id,name', 'store:id,name']);

        return $this->jsonOk($request, ['message' => 'Product updated.', 'product' => $product], redirect()->route('products.index')->with('status', 'Product updated.'));
    }

    public function destroy(Request $request, Product $product): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $product->delete();

        return $this->jsonOk($request, ['message' => 'Product removed.'], redirect()->route('products.index')->with('status', 'Product removed.'));
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

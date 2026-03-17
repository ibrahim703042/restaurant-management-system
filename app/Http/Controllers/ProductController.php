<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {

        $products = DB::table('products')->get();
        return view('pages.tables.productTable', compact('products'));
    }

    public function create()
    {
        return view('pages.forms.product');
    }


    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

       $image_path = '';
       if ($request->hasFile('image')) {
           $image_path = $request->file('image')->store('image', 'public');
        }

		$productData = [

        'product_name' => $request->name,
        'price' => $request->price,
        'description' => $request->description,
        'category_id' => $request->category,
        'store_id' => $request->store,
        'image' => $image_path ?: 'products/default.png',
        'status' => $request->status];

        $product = Product::create($productData);
        \App\Models\InventoryStock::query()->firstOrCreate(
            ['store_id' => $product->store_id, 'product_id' => $product->id],
            ['quantity' => 0, 'reorder_level' => 0]
        );

        return redirect()->route('products.index')->with('status', 'Product Created Successfully');

    }

    public function show()
    {
        //
    }

    public function edit($id)
    {
        $product = Product::find($id);
        return view('pages.forms.edit_product', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $product->product_name = $request->input('name');
        $product->price = $request->input('price');
        $product->description = $request->input('description');
        $product->category_id = $request->input('category');
        $product->store_id = $request->input('store');
        $product->status = $request->input('status');
        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }
        $product->save();

        return redirect()->route('products.index')->with('status','Product Updated Successfully');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->back()->with('status','Product Deleted Successfully');
    }
}

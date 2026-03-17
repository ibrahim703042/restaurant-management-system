<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->get();

        return view('pages.tables.storeTable', compact('stores'));
    }

    public function create()
    {
        return view('pages.forms.store');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:64',
            'notes' => 'nullable|string|max:2000',
            'is_primary_stock_location' => 'nullable|boolean',
            'status' => 'required|integer',
        ]);

        Store::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_primary_stock_location' => $request->boolean('is_primary_stock_location', true),
            'status' => $data['status'],
        ]);

        return redirect()->route('stores.index')->with('status', 'Store created.');
    }

    public function edit($id)
    {
        $store = Store::findOrFail($id);

        return view('pages.forms.edit_store', compact('store'));
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:64',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|integer',
        ]);
        $store->fill($data);
        $store->is_primary_stock_location = $request->boolean('is_primary_stock_location', false);
        $store->save();

        return redirect()->route('stores.index')->with('status', 'Store updated.');
    }

    public function destroy($id)
    {
        Store::findOrFail($id)->delete();

        return redirect()->back()->with('status', 'Store removed.');
    }
}

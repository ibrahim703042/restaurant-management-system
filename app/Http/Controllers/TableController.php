<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Store;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = DiningTable::query()
            ->join('stores', 'stores.id', '=', 'tables.store_id')
            ->orderBy('tables.sort_order')
            ->orderBy('tables.table_name')
            ->get(['tables.*', 'stores.name as store_name']);

        return view('pages.tables.tableTable', compact('tables'));
    }

    public function create()
    {
        return view('pages.forms.table');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|string|max:32',
            'status' => 'required|integer',
            'store' => 'required|exists:stores,id',
            'section' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        DiningTable::create([
            'table_name' => $request->name,
            'section' => $request->section,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'capacity' => $request->capacity,
            'status' => $request->status,
            'store_id' => $request->store,
        ]);

        return redirect()->route('tables.index')->with('status', 'Table created.');
    }

    public function edit($id)
    {
        $table = DiningTable::findOrFail($id);

        return view('pages.forms.edit_table', compact('table'));
    }

    public function update(Request $request, $id)
    {
        $table = DiningTable::findOrFail($id);
        $table->update([
            'table_name' => $request->input('name'),
            'section' => $request->input('section'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'capacity' => $request->input('capacity'),
            'status' => $request->input('status'),
            'store_id' => $request->input('store'),
        ]);

        return redirect()->route('tables.index')->with('status', 'Table updated.');
    }

    public function destroy($id)
    {
        DiningTable::findOrFail($id)->delete();

        return redirect()->back()->with('status', 'Table removed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.clients.manage']);
    }

    public function index()
    {
        $clients = Client::orderByDesc('id')->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);
        Client::create($data);

        return redirect()->route('clients.index')->with('status', 'Client created.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);
        $client->update($data);

        return redirect()->route('clients.index')->with('status', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        if ($client->orders()->exists()) {
            return back()->withErrors(['delete' => 'Client has orders and cannot be deleted.']);
        }
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client removed.');
    }
}

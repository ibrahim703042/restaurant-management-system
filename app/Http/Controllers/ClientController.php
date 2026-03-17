<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.clients.manage']);
    }

    public function index()
    {
        return view('clients.manage');
    }

    public function listJson(Request $request): JsonResponse
    {
        $q = Client::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = '%'.$request->search.'%';
                $query->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)->orWhere('phone', 'like', $s)->orWhere('address', 'like', $s);
                });
            });

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(Client $client): JsonResponse
    {
        return response()->json(['client' => $client]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $client = Client::create($data);

        return $this->jsonOk($request, ['message' => 'Client created.', 'client' => $client], redirect()->route('clients.index')->with('status', 'Client created.'));
    }

    public function update(Request $request, Client $client): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $client->update($data);

        return $this->jsonOk($request, ['message' => 'Client updated.', 'client' => $client->fresh()], redirect()->route('clients.index')->with('status', 'Client updated.'));
    }

    public function destroy(Request $request, Client $client): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($client->orders()->exists()) {
            $msg = 'Client has orders and cannot be deleted.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $msg, 'errors' => ['client' => [$msg]]], 422);
            }

            return back()->withErrors(['delete' => $msg]);
        }
        $client->delete();

        return $this->jsonOk($request, ['message' => 'Client removed.'], redirect()->route('clients.index')->with('status', 'Client removed.'));
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

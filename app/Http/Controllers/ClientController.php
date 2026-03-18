<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        return $this->dataTablesOf(
            Client::query(),
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)->orWhere('phone', 'like', $s)->orWhere('address', 'like', $s);
                });
            },
            fn (Builder $q) => $q->orderByDesc('id'),
            function (Client $row) {
                $addr = e(Str::limit((string) $row->address, 60));

                return [
                    'id' => $row->id,
                    'name' => e($row->name),
                    'phone' => e((string) $row->phone),
                    'address' => $addr,
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Client $client): JsonResponse
    {
        return response()->json(['client' => $client]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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

    public function update(Request $request, Client $client): JsonResponse|RedirectResponse
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

    public function destroy(Request $request, Client $client): JsonResponse|RedirectResponse
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

    public function destroyBulk(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = \App\Models\Client::whereIn('id', $data['ids'])->delete();
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

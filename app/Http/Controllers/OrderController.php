<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.orders.manage']);
    }

    public function index(): View
    {
        return view('orders.index');
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Order::query()->with(['client:id,name', 'user:id,name', 'bill:id,order_id,bill_number']);

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('order_number', 'like', $s)
                        ->orWhere('status', 'like', $s)
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', $s));
                });
            },
            fn (Builder $q) => $q->orderByDesc('id'),
            function (Order $o) {
                $viewUrl = route('orders.show', $o);
                $hasBill = $o->bill !== null;
                $editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-order" data-id="'.$o->id.'" title="'.e(__('orders.edit_notes')).'"><i class="fas fa-edit"></i></button>';
                $delBtn = $hasBill
                    ? '<span class="text-muted small" title="'.e(__('orders.has_bill')).'">—</span>'
                    : '<button type="button" class="btn btn-sm btn-outline-danger btn-del-order" data-id="'.$o->id.'"><i class="fas fa-trash"></i></button>';

                return [
                    'id' => $o->id,
                    'order_number' => e($o->order_number),
                    'client' => e($o->client?->name ?? '—'),
                    'total' => number_format((float) $o->total, 0),
                    'status' => e($o->status),
                    'created_at' => $o->created_at->format('Y-m-d H:i'),
                    'actions' => '<div class="btn-group btn-group-sm">'
                        .'<a href="'.e($viewUrl).'" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>'
                        .$editBtn.$delBtn
                        .'</div>',
                ];
            }
        );
    }

    public function showJson(Order $order): JsonResponse
    {
        return response()->json(['order' => $order->only(['id', 'order_number', 'notes'])]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $order->update(['notes' => $data['notes'] ?? null]);

        return response()->json(['message' => __('orders.notes_saved'), 'order' => $order->fresh()]);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        if ($order->bill()->exists()) {
            return response()->json(['message' => __('orders.cannot_delete_billed')], 422);
        }
        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => __('orders.deleted')]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'client', 'user', 'bill.payments', 'bill.debts']);

        return view('orders.show', compact('order'));
    }
}

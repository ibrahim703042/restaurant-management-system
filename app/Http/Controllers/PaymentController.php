<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.payments.manage']);
    }

    public function index()
    {
        return view('payments.index');
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Payment::query()->with(['bill.order', 'client', 'user']);

        return $this->dataTablesOf(
            $base,
            $request,
            function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('bill', fn ($b) => $b->where('bill_number', 'like', "%{$search}%"))
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%");
                });
            },
            function ($q) use ($request) {
                $col = (int) $request->input('order.0.column', 0);
                $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $map = [0 => 'payments.id', 1 => 'payments.created_at', 4 => 'payments.amount'];
                $q->orderBy($map[$col] ?? 'payments.id', $dir);
            },
            function ($p) {
                $billUrl = $p->bill_id ? route('bills.show', $p->bill_id) : '#';
                $printUrl = $p->bill_id ? route('bills.print', $p->bill_id) : '#';

                return [
                    'id' => $p->id,
                    'created_at' => $p->created_at->format('Y-m-d H:i'),
                    'bill_html' => '<a href="'.e($billUrl).'">'.e($p->bill->bill_number ?? '—').'</a>',
                    'bill_link' => $billUrl,
                    'client' => e($p->client?->name ?? '—'),
                    'amount' => number_format((float) $p->amount, 0),
                    'method' => e($p->payment_method),
                    'user' => e($p->user->name ?? '—'),
                    'actions' => '<div class="btn-group btn-group-sm">'
                        .'<a href="'.e($billUrl).'" class="btn btn-outline-primary" title="Bill"><i class="fas fa-file-invoice"></i></a>'
                        .'<a href="'.e($printUrl).'" target="_blank" class="btn btn-outline-secondary" title="Print"><i class="fas fa-print"></i></a>'
                        .'</div>',
                ];
            }
        );
    }
}

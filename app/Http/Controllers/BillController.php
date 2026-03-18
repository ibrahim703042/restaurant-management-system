<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Bill;
use App\Support\QrCodeSvg;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.bills.manage']);
    }

    public function index()
    {
        return view('bills.index');
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Bill::query()->with(['order.client']);

        return $this->dataTablesOf(
            $base,
            $request,
            function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('bill_number', 'like', "%{$search}%")
                        ->orWhere('payment_status', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$search}%"));
                });
            },
            function ($q) use ($request) {
                $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $q->orderBy('id', $dir);
            },
            function (Bill $b) {
                $badge = $b->payment_status === 'paid' ? 'success' : ($b->payment_status === 'partial' ? 'warning' : 'secondary');
                $show = route('bills.show', $b);
                $print = route('bills.print', $b);

                return [
                    'id' => $b->id,
                    'bill_number' => e($b->bill_number),
                    'order_number' => e($b->order->order_number ?? '—'),
                    'total' => number_format((float) $b->total, 0),
                    'status_html' => '<span class="badge bg-'.$badge.'">'.e($b->payment_status).'</span>',
                    'client' => e($b->order->client->name ?? '—'),
                    'actions' => '<div class="btn-group btn-group-sm">'
                        .'<a href="'.e($show).'" class="btn btn-outline-primary" title="Open"><i class="fas fa-eye"></i></a>'
                        .'<a href="'.e($print).'" target="_blank" class="btn btn-outline-secondary" title="Print"><i class="fas fa-print"></i></a>'
                        .'<a href="'.e($show).'" class="btn btn-outline-info" title="QR / details"><i class="fas fa-qrcode"></i></a>'
                        .'</div>',
                ];
            }
        );
    }

    public function show(Bill $bill)
    {
        $bill->load(['order.items.product', 'order.client', 'order.diningTable', 'payments', 'debts']);
        $verifyUrl = $bill->verifyUrl();
        $qrSvg = QrCodeSvg::forData($verifyUrl, 180, 6);

        return view('bills.show', compact('bill', 'verifyUrl', 'qrSvg'));
    }

    public function printView(Bill $bill)
    {
        $bill->load(['order.items.product', 'order.client', 'order.diningTable']);
        $verifyUrl = $bill->verifyUrl();
        $qrSvg = QrCodeSvg::forData($verifyUrl, 140, 4);

        return view('bills.print', compact('bill', 'verifyUrl', 'qrSvg'));
    }
}

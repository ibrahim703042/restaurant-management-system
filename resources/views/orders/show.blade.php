@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)
@section('page-title', 'Order '.$order->order_number)
@section('main-section')
@php
    $bill = $order->bill;
    $paid = $bill ? $bill->totalPaid() : 0;
    $remaining = $bill ? $bill->remainingAmount() : 0;
    $debts = $bill ? $bill->debts : collect();
@endphp
<div class="container-fluid px-4">
    <h1 class="mt-4">Order {{ $order->order_number }}</h1>
    <p>Client: {{ $order->client?->name ?? 'Walk-in' }} | Cashier: {{ $order->user->name }}</p>

    @if($bill)
    <div class="alert alert-light border d-flex flex-wrap align-items-center gap-3 mb-3">
        <div>
            <strong>Bill {{ $bill->bill_number }}</strong>
            <span class="badge bg-secondary ms-1">{{ $bill->payment_status }}</span>
        </div>
        <div>Total: <strong>{{ number_format($bill->total, 0) }}</strong></div>
        <div>Paid: <strong class="text-success">{{ number_format($paid, 0) }}</strong></div>
        <div>@if($remaining > 0.009)<span class="text-danger fw-bold">Remaining: {{ number_format($remaining, 0) }}</span>@else<span class="text-muted">Fully paid</span>@endif</div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderBillModal"><i class="fas fa-receipt me-1"></i>Bill &amp; payments</button>
        <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-secondary">Open bill</a>
    </div>
    @endif

    <div class="card"><div class="card-body">
        <table class="table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Line</th></tr></thead>
        <tbody>
            @foreach ($order->items as $line)
            <tr>
                <td>{{ $line->product->product_name }}</td>
                <td>{{ $line->quantity }}</td>
                <td>{{ number_format($line->unit_price, 0) }}</td>
                <td>{{ number_format($line->line_total, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($order->total, 0) }}</th></tr></tfoot>
        </table>
        @if(!$order->bill)
        <p class="text-muted mb-0">No bill linked.</p>
        @endif
    </div></div>
</div>

@if($bill)
<div class="modal fade" id="orderBillModal" tabindex="-1" aria-labelledby="orderBillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderBillModalLabel">Bill {{ $bill->bill_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="p-2 bg-light rounded"><small class="text-muted">Total</small><div class="fs-5 fw-bold">{{ number_format($bill->total, 0) }}</div></div></div>
                    <div class="col-md-4"><div class="p-2 bg-light rounded"><small class="text-muted">Paid</small><div class="fs-5 text-success fw-bold">{{ number_format($paid, 0) }}</div></div></div>
                    <div class="col-md-4"><div class="p-2 bg-light rounded border @if($remaining > 0.009) border-danger @endif"><small class="text-muted">Remaining / debt</small><div class="fs-5 fw-bold @if($remaining > 0.009) text-danger @else text-muted @endif">{{ number_format($remaining, 0) }}</div></div></div>
                </div>
                <h6 class="border-bottom pb-2">Payments</h6>
                @if($bill->payments->isEmpty())
                <p class="text-muted">No payments recorded yet.</p>
                @else
                <ul class="list-group list-group-flush mb-3">
                    @foreach($bill->payments as $p)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ number_format($p->amount, 0) }} <small class="text-muted">({{ $p->payment_method }})</small></span>
                        <small>{{ $p->created_at->format('Y-m-d H:i') }}</small>
                    </li>
                    @endforeach
                </ul>
                @endif
                <h6 class="border-bottom pb-2">Debt records (this bill)</h6>
                @if($debts->isEmpty())
                <p class="text-muted small">No debt row — full payment or walk-in without account.</p>
                @else
                <table class="table table-sm">
                    <thead><tr><th>Status</th><th>Owed</th><th>Paid</th><th>Balance</th></tr></thead>
                    <tbody>
                        @foreach($debts as $d)
                        <tr>
                            <td><span class="badge bg-{{ $d->status === 'open' ? 'warning' : 'secondary' }}">{{ $d->status }}</span></td>
                            <td>{{ number_format($d->amount_owed, 0) }}</td>
                            <td>{{ number_format($d->amount_paid, 0) }}</td>
                            <td class="fw-bold @if($d->balance > 0) text-danger @endif">{{ number_format($d->balance, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                <p class="small text-muted mb-0">Verify URL: <a href="{{ $bill->verifyUrl() }}" target="_blank">{{ $bill->verifyUrl() }}</a></p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('bills.print', $bill) }}" target="_blank" class="btn btn-outline-secondary">Print</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@if($remaining > 0.009)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('orderBillModal');
    if (el && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(el).show();
    }
});
</script>
@endpush
@endif
@endif
@endsection

@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Bill {{ $bill->bill_number }}</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <p><strong>Total:</strong> {{ number_format($bill->total, 0) }}</p>
                <p><strong>Status:</strong> {{ $bill->payment_status }}</p>
                <p class="small text-break"><strong>Verify URL:</strong><br><a href="{{ $bill->verifyUrl() }}">{{ $bill->verifyUrl() }}</a></p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($bill->verifyUrl()) }}" alt="QR" class="img-thumbnail">
                <div class="mt-2">
                    <a href="{{ route('bills.print', $bill) }}" target="_blank" class="btn btn-secondary">Print receipt</a>
                </div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-header">Payments</div><div class="card-body">
                <ul class="list-group">
                    @forelse ($bill->payments as $p)
                    <li class="list-group-item">{{ number_format($p->amount, 0) }} — {{ $p->payment_method }} ({{ $p->created_at->format('d/m H:i') }})</li>
                    @empty
                    <li class="list-group-item text-muted">No payments yet</li>
                    @endforelse
                </ul>
            </div></div>
        </div>
    </div>
</div>
@endsection

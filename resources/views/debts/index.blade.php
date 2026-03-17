@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Open debts</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="card mt-3"><div class="card-body">
        @foreach ($debts as $d)
        <div class="border rounded p-3 mb-3">
            <strong>{{ $d->client->name }}</strong> — Bill {{ $d->bill->bill_number }} — Balance <strong>{{ number_format($d->balance, 0) }}</strong>
            <form method="post" action="{{ route('debts.pay', $d) }}" class="row g-2 mt-2 align-items-end">
                @csrf
                <div class="col-auto"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" max="{{ $d->balance }}" required></div>
                <div class="col-auto">
                    <select name="payment_method" class="form-select"><option value="cash">Cash</option><option value="mobile_money">Mobile money</option><option value="bank">Bank</option></select>
                </div>
                <div class="col-auto"><button class="btn btn-primary">Pay</button></div>
            </form>
        </div>
        @endforeach
        @if ($debts->isEmpty())<p class="text-muted">No open debts.</p>@endif
        {{ $debts->links() }}
    </div></div>
</div>
@endsection

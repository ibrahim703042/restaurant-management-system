@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Payments</h1>
    <div class="card mt-3"><div class="card-body table-responsive">
        <table class="table table-sm"><thead><tr><th>Date</th><th>Bill</th><th>Client</th><th>Amount</th><th>Method</th><th>User</th></tr></thead>
        <tbody>
            @foreach ($payments as $p)
            <tr>
                <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $p->bill->bill_number ?? $p->bill_id }}</td>
                <td>{{ $p->client?->name ?? '—' }}</td>
                <td>{{ number_format($p->amount, 0) }}</td>
                <td>{{ $p->payment_method }}</td>
                <td>{{ $p->user->name }}</td>
            </tr>
            @endforeach
        </tbody></table>
        {{ $payments->links() }}
    </div></div>
</div>
@endsection

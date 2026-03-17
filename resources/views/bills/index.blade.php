@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Bills</h1>
    <div class="card mt-3"><div class="card-body table-responsive">
        <table class="table table-sm"><thead><tr><th>Bill</th><th>Order</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($bills as $b)
            <tr>
                <td>{{ $b->bill_number }}</td>
                <td>{{ $b->order->order_number }}</td>
                <td>{{ number_format($b->total, 0) }}</td>
                <td><span class="badge bg-{{ $b->payment_status === 'paid' ? 'success' : ($b->payment_status === 'partial' ? 'warning' : 'secondary') }}">{{ $b->payment_status }}</span></td>
                <td>
                    <a href="{{ route('bills.show', $b) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    <a href="{{ route('bills.print', $b) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Print</a>
                </td>
            </tr>
            @endforeach
        </tbody></table>
        {{ $bills->links() }}
    </div></div>
</div>
@endsection

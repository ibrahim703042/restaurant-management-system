@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Orders</h1>
    <div class="card mt-3"><div class="card-body table-responsive">
        <table class="table"><thead><tr><th>#</th><th>Order</th><th>Client</th><th>Total</th><th>Date</th><th></th></tr></thead>
        <tbody>
            @foreach ($orders as $o)
            <tr>
                <td>{{ $o->id }}</td>
                <td>{{ $o->order_number }}</td>
                <td>{{ $o->client?->name ?? '—' }}</td>
                <td>{{ number_format($o->total, 0) }}</td>
                <td>{{ $o->created_at->format('Y-m-d H:i') }}</td>
                <td><a href="{{ route('orders.show', $o) }}" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            @endforeach
        </tbody></table>
        {{ $orders->links() }}
    </div></div>
</div>
@endsection

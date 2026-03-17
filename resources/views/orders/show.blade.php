@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Order {{ $order->order_number }}</h1>
    <p>Client: {{ $order->client?->name ?? 'Walk-in' }} | Cashier: {{ $order->user->name }}</p>
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
        @if($order->bill)
        <a href="{{ route('bills.show', $order->bill) }}" class="btn btn-primary">View bill</a>
        @endif
    </div></div>
</div>
@endsection

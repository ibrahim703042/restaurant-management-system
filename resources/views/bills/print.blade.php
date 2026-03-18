<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $bill->bill_number }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
body {
    font-family: "Courier New", monospace;
    width: 280px;
    margin: auto;
    padding: 8px;
    font-size: 12px;
    color: #000;
}

.center { text-align: center; }

.logo-mark {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    color: #ceaab4;
    line-height: 1;
}

.logo-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 2px;
}

.divider {
    border-top: 1px dashed #000;
    margin: 6px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th, td {
    padding: 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.col-name  { width: 55%; }
.col-qty   { width: 15%; text-align: center; }
.col-price { width: 30%; text-align: right; }

.summary td { padding: 3px 0; }

.bold { font-weight: bold; }
.big  { font-size: 14px; }

.qr {
    text-align: center;
    margin-top: 10px;
}
.qr svg { max-width: 140px; height: auto; }

.footer {
    text-align: center;
    margin-top: 8px;
    font-size: 11px;
}

@media print {
    body { padding: 0; margin: 0 auto; }
}
</style>
</head>

<body onload="window.print()">

<div class="center">
    <div class="logo-mark">Rg</div>
    <div class="logo-name">Bar Restaurant</div>
    <div style="margin-top:4px">
        Bill: <span class="bold">{{ $bill->bill_number }}</span><br>
        {{ $bill->created_at->format('d/m/Y H:i') }}
    </div>
</div>

<div class="divider"></div>

<table>
    <thead>
        <tr class="bold">
            <th class="col-name">Name</th>
            <th class="col-qty">Qty</th>
            <th class="col-price">Total</th>
        </tr>
    </thead>
</table>

<div class="divider"></div>

<table>
    <tbody>
        @foreach ($bill->order->items as $line)
        <tr>
            <td class="col-name">{{ $line->product->product_name }}</td>
            <td class="col-qty">{{ $line->quantity }}</td>
            <td class="col-price">{{ number_format($line->line_total, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

@php
    $paid = $bill->payments->sum('amount');
    $change = max($paid - $bill->total, 0);
@endphp

<table class="summary">
    <tr>
        <td>Sub Total</td>
        <td class="col-price">{{ number_format($bill->total, 0) }}</td>
    </tr>
    <tr>
        <td>Paid</td>
        <td class="col-price">{{ number_format($paid, 0) }}</td>
    </tr>
    <tr>
        <td>Change</td>
        <td class="col-price">{{ number_format($change, 0) }}</td>
    </tr>
</table>

<div class="divider"></div>

<table>
    <tr class="bold big">
        <td>Total</td>
        <td class="col-price">{{ number_format($bill->total, 0) }}</td>
    </tr>
</table>

<div class="divider"></div>

    <div class="center" style="font-size:11px;margin-bottom:2px">
        Client: {{ $bill->order->client?->name ?? 'Walk-in' }}
        @if($bill->order->diningTable)
        <br>Table: {{ $bill->order->diningTable->table_name }}
        @endif
    </div>

<div class="qr">
    {!! $qrSvg !!}
</div>

<div class="footer">
    Thank You!<br>
    Please Come Again
</div>

</body>
</html>

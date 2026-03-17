<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Bill {{ $bill->bill_number }}</title>
<style>body{font-family:sans-serif;max-width:320px;margin:1rem auto;} table{width:100%;} th{text-align:left;}</style>
</head><body>
<h2>{{ config('app.name') }}</h2>
<p>Bill: <strong>{{ $bill->bill_number }}</strong><br>{{ $bill->created_at->format('Y-m-d H:i') }}</p>
<hr>
<table>
@foreach ($bill->order->items as $line)
<tr><td>{{ $line->product->product_name }} × {{ $line->quantity }}</td><td style="text-align:right">{{ number_format($line->line_total, 0) }}</td></tr>
@endforeach
</table>
<hr>
<p style="text-align:right"><strong>Total {{ number_format($bill->total, 0) }}</strong></p>
<p>Client: {{ $bill->order->client?->name ?? 'Walk-in' }}</p>
<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($bill->verifyUrl()) }}" alt="QR">
<p class="small">Scan to verify</p>
<script>window.onload=function(){window.print();}</script>
</body></html>

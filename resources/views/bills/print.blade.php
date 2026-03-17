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
<div style="text-align:center;margin:8px 0">{!! $qrSvg !!}</div>
<p class="small" style="word-break:break-all;font-size:10px">{{ $verifyUrl }}</p>
<p class="small">Scan to verify / pay</p>
<script>window.onload=function(){window.print();}</script>
</body></html>

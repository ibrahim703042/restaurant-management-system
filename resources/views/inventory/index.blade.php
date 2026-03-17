@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Stock by store</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="get" class="mb-3">
        <label class="form-label">Store / stock location</label>
        <select name="store_id" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            @foreach ($stores as $s)
            <option value="{{ $s->id }}" {{ (string)$storeId === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </form>
    @if (!$storeId)
    <p class="text-muted">Create a store first.</p>
    @else
    <div class="table-responsive card">
        <table class="table table-sm mb-0">
            <thead><tr><th>Product</th><th>Qty</th><th>Reorder</th><th>Adjust</th></tr></thead>
            <tbody>
                @foreach ($stocks as $row)
                <tr>
                    <td>{{ $row->product->product_name ?? '—' }}</td>
                    <td>{{ number_format($row->quantity, 2) }}</td>
                    <td>{{ number_format($row->reorder_level, 2) }}</td>
                    <td>
                        @can('inventory.stock.adjust')
                        <form method="post" action="{{ route('inventory.adjust') }}" class="d-flex gap-1 flex-wrap">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $storeId }}">
                            <input type="hidden" name="product_id" value="{{ $row->product_id }}">
                            <input type="number" step="0.001" name="delta" class="form-control form-control-sm" style="width:90px" placeholder="+/−" required>
                            <button class="btn btn-sm btn-primary">Apply</button>
                        </form>
                        @else
                        <span class="text-muted">—</span>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

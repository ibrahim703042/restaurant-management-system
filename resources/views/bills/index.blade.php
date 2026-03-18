@extends('layouts.admin')
@section('title', 'Bills')
@section('page-title', 'Bills')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Bills</li>
@endsection

@section('main-section')
<x-admin.table-card title="All bills" icon="fas fa-file-invoice-dollar">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Bill</th>
            <th>Order</th>
            <th>Client</th>
            <th>Total</th>
            <th>Status</th>
            <th style="width:140px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>
@endsection

@push('scripts')
<script>
(function () {
    $('#dt-table').DataTable({
        serverSide: true,
        ajax: @json(route('bills.list')),
        columns: [
            { data: 'id' },
            { data: 'bill_number' },
            { data: 'order_number' },
            { data: 'client' },
            { data: 'total' },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });
})();
</script>
@endpush

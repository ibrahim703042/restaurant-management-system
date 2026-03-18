@extends('layouts.admin')
@section('title', 'Payments')
@section('page-title', 'Payments')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Payments</li>
@endsection

@section('main-section')
<x-admin.table-card title="Payment history" icon="fas fa-money-bill-wave">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Bill</th>
            <th>Client</th>
            <th>Amount</th>
            <th>Method</th>
            <th>User</th>
            <th style="width:100px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>
@endsection

@push('scripts')
<script>
(function () {
    $('#dt-table').DataTable({
        serverSide: true,
        ajax: @json(route('payments.list')),
        columns: [
            { data: 'id' },
            { data: 'created_at' },
            { data: 'bill_html', orderable: false, searchable: false },
            { data: 'client' },
            { data: 'amount' },
            { data: 'method' },
            { data: 'user' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });
})();
</script>
@endpush

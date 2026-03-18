@extends('layouts.admin')
@section('title', 'Staff activity')
@section('page-title', 'Staff activity log')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Activity</li>
@endsection

@section('main-section')
<x-admin.table-card title="Actions by linked staff accounts" icon="fas fa-running">
    <x-slot:filters>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Employee</label>
                <select class="form-select form-select-sm" id="filterEmployee">
                    <option value="">All</option>
                    @foreach ($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="btnFilter">Apply</button></div>
        </div>
    </x-slot:filters>
    <p class="text-muted small mb-2">Only users with a linked employee profile are logged (POS checkout, debt payments, etc.).</p>
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>When</th>
            <th>Employee</th>
            <th>User</th>
            <th>Action</th>
            <th>Description</th>
        </tr>
    </thead>
</x-admin.table-card>
@endsection

@push('scripts')
<script>
(function () {
    const listUrl = @json(route('employee-activities.list'));
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: {
            url: listUrl,
            data: function (d) { d.employee_id = document.getElementById('filterEmployee').value; }
        },
        columns: [
            { data: 'id' }, { data: 'at' }, { data: 'employee' }, { data: 'user' }, { data: 'action' }, { data: 'description' }
        ],
        order: [[1, 'desc']]
    });
    document.getElementById('btnFilter').addEventListener('click', function () { dt.ajax.reload(); });
})();
</script>
@endpush

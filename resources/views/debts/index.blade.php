@extends('layouts.admin')
@section('title', 'Debts')
@section('page-title', 'Open debts')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Debts</li>
@endsection

@section('main-section')
@if (session('status'))
<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<x-admin.table-card title="Outstanding client debts" icon="fas fa-hand-holding-usd">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Bill</th>
            <th>Balance</th>
            <th>Owed</th>
            <th>Paid</th>
            <th style="width:120px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

<div class="modal fade" id="debtPayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="debtPayForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong id="debtPayClient"></strong> — balance <strong id="debtPayBalance"></strong></p>
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="debtPayAmount" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile money</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: @json(route('debts.list')),
        columns: [
            { data: 'id' },
            { data: 'client' },
            { data: 'bill' },
            { data: 'balance' },
            { data: 'owed' },
            { data: 'paid' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });
    const payModal = new bootstrap.Modal(document.getElementById('debtPayModal'));
    const form = document.getElementById('debtPayForm');
    const amt = document.getElementById('debtPayAmount');
    $('#dt-table tbody').on('click', '.btn-pay-debt', function () {
        const btn = $(this);
        form.action = btn.data('pay-url');
        document.getElementById('debtPayClient').textContent = btn.data('client') || '';
        document.getElementById('debtPayBalance').textContent = btn.data('balance') || '';
        amt.max = btn.data('balance');
        amt.value = btn.data('balance');
        payModal.show();
    });
    form.addEventListener('submit', function () {
        payModal.hide();
    });
})();
</script>
@endpush

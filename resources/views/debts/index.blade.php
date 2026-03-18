@extends('layouts.admin')
@section('title', __('debts.title'))
@section('page-title', __('debts.page_title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('debts.title') }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <x-admin.page-actions :title="__('debts.page_title')" />

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('debts.search') }}">
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('common.id') }}</th>
                    <th>{{ __('debts.col_client') }}</th>
                    <th>{{ __('debts.col_bill') }}</th>
                    <th>{{ __('debts.col_balance') }}</th>
                    <th>{{ __('debts.col_owed') }}</th>
                    <th>{{ __('debts.col_paid') }}</th>
                    <th class="text-end pe-4" style="width:120px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>

    <div class="modal fade" id="debtPayModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dt-pro-modal border-0 shadow rounded-4">
                <form method="post" id="debtPayForm">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">{{ __('debts.modal_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-3 text-muted"><strong id="debtPayClient"></strong> — {{ __('debts.col_balance') }} <strong id="debtPayBalance"></strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('debts.label_amount') }}</label>
                            <input type="number" step="0.01" name="amount" id="debtPayAmount" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">{{ __('debts.label_method') }}</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">{{ __('debts.method_cash') }}</option>
                                <option value="mobile_money">{{ __('debts.method_mobile') }}</option>
                                <option value="bank">{{ __('debts.method_bank') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('debts.btn_pay') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var dt = $('#dt-table').DataTable({
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
        pageLength: 25,
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    var payModal = new bootstrap.Modal(document.getElementById('debtPayModal'));
    var form = document.getElementById('debtPayForm');
    var amt = document.getElementById('debtPayAmount');

    $('#dt-table tbody').on('click', '.btn-pay-debt', function () {
        var btn = $(this);
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

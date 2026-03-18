@extends('layouts.admin')
@section('title', __('payments.title'))
@section('page-title', __('payments.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('payments.title') }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('payments.title')" />

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('payments.search') }}">
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('common.id') }}</th>
                    <th>{{ __('payments.col_date') }}</th>
                    <th>{{ __('payments.col_bill') }}</th>
                    <th>{{ __('payments.col_client') }}</th>
                    <th>{{ __('payments.col_amount') }}</th>
                    <th>{{ __('payments.col_method') }}</th>
                    <th>{{ __('payments.col_user') }}</th>
                    <th class="text-end pe-4" style="width:100px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var dt = $('#dt-table').DataTable({
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
        pageLength: 25,
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });
})();
</script>
@endpush

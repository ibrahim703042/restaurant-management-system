@extends('layouts.admin')
@section('title', __('activities.title'))
@section('page-title', __('activities.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('activities.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('activities.title')" />

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('activities.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select" id="filterEmployee">
                        <option value="">{{ __('activities.filter_all') }}</option>
                        @foreach ($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-dt-pro-outline btn-sm px-3" id="btnFilter">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('common.apply_filter') }}
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('common.id') }}</th>
                    <th>{{ __('activities.col_when') }}</th>
                    <th>{{ __('activities.col_employee') }}</th>
                    <th>{{ __('activities.col_user') }}</th>
                    <th>{{ __('activities.col_action') }}</th>
                    <th>{{ __('activities.col_description') }}</th>
                </tr>
            </thead>
        </table>
        <x-slot:footer>
            <div class="small text-muted">{{ __('activities.hint') }}</div>
        </x-slot:footer>
    </x-admin.data-table-pro>
</div>
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
        order: [[1, 'desc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    document.getElementById('btnFilter').addEventListener('click', function () { dt.ajax.reload(); });
})();
</script>
@endpush

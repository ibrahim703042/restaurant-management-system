@extends('layouts.admin')
@section('title', __('payroll.title'))
@section('page-title', __('payroll.subtitle'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('payroll.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('payroll.subtitle')">
        <x-slot:actions>
            @can('hr.payroll.manage')
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('payroll.add') }}
            </button>
            @endcan
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('payroll.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @cannot('hr.payroll.manage')
                    <span class="text-muted small">{{ __('payroll.no_permission') }}</span>
                    @endcannot
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('common.id') }}</th>
                    <th>{{ __('payroll.col_start') }}</th>
                    <th>{{ __('payroll.col_end') }}</th>
                    <th>{{ __('payroll.col_status') }}</th>
                    <th>{{ __('payroll.col_notes') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

@can('hr.payroll.manage')
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold">{{ __('payroll.add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <x-admin.input name="period_start" type="date" :label="__('payroll.label_start')" required />
                    <x-admin.input name="period_end" type="date" :label="__('payroll.label_end')" required />
                    <x-admin.textarea name="notes" :label="__('payroll.label_notes')" rows="2" />
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
@push('scripts')
<script>
(function () {
    const listUrl = @json(route('payroll.list'));
    const dtPay = $('#dt-table').DataTable({
        serverSide: true, ajax: { url: listUrl },
        columns: [
            { data: 'id', className: 'ps-4' }, { data: 'period_start' }, { data: 'period_end' }, { data: 'status' }, { data: 'notes' }
        ],
        order: [[0, 'desc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dtPay.search(searchInput.value).draw(); }, 350);
    });

    @can('hr.payroll.manage')
    const storeUrl = @json(route('payroll.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const modal = new bootstrap.Modal(document.getElementById('modal'));
    const form = document.getElementById('form');
    document.getElementById('btnAdd').addEventListener('click', function () {
        form.reset(); document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        fetch(storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || @json(__('common.created')), timer: 1500, showConfirmButton: false });
                dtPay.ajax.reload(null, false);
            });
    });
    @endcan
})();
</script>
@endpush

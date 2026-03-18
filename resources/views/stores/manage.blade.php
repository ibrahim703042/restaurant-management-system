@extends('layouts.admin')
@section('title', __('stores.title'))
@section('page-title', __('stores.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('stores.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('stores.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('stores.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('stores.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button class="btn btn-danger btn-sm d-none" id="btnBulkDel">
                        <i class="fas fa-trash me-1"></i>{{ __('common.delete_selected') }} <span class="dt-pro-bulk-count badge bg-white text-danger ms-1"></span>
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:42px"><input type="checkbox" class="form-check-input" id="dtCheckAll"></th>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('stores.col_name') }}</th>
                    <th>{{ __('stores.col_code') }}</th>
                    <th>{{ __('stores.col_phone') }}</th>
                    <th>{{ __('stores.col_primary_stock') }}</th>
                    <th>{{ __('stores.col_status') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">{{ __('stores.add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="name" :label="__('stores.label_name')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="code" :label="__('stores.label_code')" wrapperClass="mb-0" /></div>
                        <div class="col-md-6"><x-admin.input name="phone" :label="__('stores.label_phone')" wrapperClass="mb-0" /></div>
                        <div class="col-md-6"><x-admin.input name="status" type="number" :label="__('stores.label_status')" value="1" wrapperClass="mb-0" required /></div>
                        <div class="col-12"><x-admin.input name="address" :label="__('stores.label_address')" wrapperClass="mb-0" /></div>
                        <div class="col-12"><x-admin.textarea name="notes" :label="__('stores.label_notes')" rows="2" wrapperClass="mb-0" /></div>
                        <div class="col-12"><x-admin.checkbox name="is_primary_stock_location" value="1" :label="__('stores.label_primary_stock')" :checked="true" id="chkPrimary" /></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
    const listUrl = @json(route('stores.list'));
    const storeUrl = @json(route('stores.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/store'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        serverSide: true, ajax: { url: listUrl },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; } },
            { data: 'id' }, { data: 'name' }, { data: 'code' }, { data: 'phone' },
            { data: 'primary_html', orderable: false, searchable: false },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[2, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    dtProBulk({ dt: dt, bulkUrl: @json(route('stores.bulkDestroy')), csrfToken: csrf, confirmMsg: @json(__('common.confirm_bulk_delete')), deleteLabel: @json(__('common.delete')) });

    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = @json(__('stores.add')); form.reset(); form.status.value = '1'; document.getElementById('chkPrimary').checked = true;
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = @json(__('stores.edit'));
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(function (d) {
            var s = d.store; form.name.value = s.name || ''; form.code.value = s.code || ''; form.phone.value = s.phone || ''; form.address.value = s.address || '';
            form.notes.value = s.notes || ''; form.status.value = s.status; document.getElementById('chkPrimary').checked = !!s.is_primary_stock_location; modal.show();
        });
    }
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        if (!document.getElementById('chkPrimary').checked) fd.append('is_primary_stock_location', '0');
        var url = editingId ? base + '/' + editingId + '/update' : storeUrl;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : (j.message || @json(__('common.error'))); err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || @json(__('common.saved')), timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: @json(__('stores.confirm_delete')), icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: @json(__('common.delete')) })
            .then(function (res) { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                        if (!r.ok) { Swal.fire(@json(__('common.error')), j.message || @json(__('stores.cannot_delete')), 'error'); return; }
                        Swal.fire({ icon: 'success', title: @json(__('common.removed')), timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

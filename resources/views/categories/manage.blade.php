@extends('layouts.admin')
@section('title', __('categories.title'))
@section('page-title', __('categories.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('categories.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('categories.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('categories.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('categories.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select" id="filterStatus">
                        <option value="">{{ __('categories.filter_all') }}</option>
                        <option value="1">{{ __('categories.filter_active') }}</option>
                        <option value="0">{{ __('categories.filter_inactive') }}</option>
                    </select>
                    <button type="button" class="btn btn-dt-pro-outline btn-sm px-3" id="btnApply">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('common.apply_filter') }}
                    </button>
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
                    <th>{{ __('categories.col_image') }}</th>
                    <th>{{ __('categories.col_name') }}</th>
                    <th>{{ __('categories.col_status') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">{{ __('categories.add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form" enctype="multipart/form-data">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <x-admin.input name="name" :label="__('categories.label_name')" required />
                    <div class="mb-3"><label class="form-label fw-semibold">{{ __('categories.label_image') }}</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <x-admin.radio-group name="status" :label="__('categories.label_status')" :options="['1' => __('common.active'), '0' => __('common.inactive')]" :selected="'1'" />
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
    const listUrl = @json(route('categories.list'));
    const storeUrl = @json(route('categories.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/category'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal'));
    const form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: {
            url: listUrl,
            data: function (d) { d.status = document.getElementById('filterStatus').value; }
        },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; } },
            { data: 'id', className: 'text-nowrap' },
            { data: 'image_html', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name' },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[3, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    dtProBulk({ dt: dt, bulkUrl: @json(route('categories.bulkDestroy')), csrfToken: csrf, confirmMsg: @json(__('common.confirm_bulk_delete')), deleteLabel: @json(__('common.delete')) });

    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = @json(__('categories.add')); form.reset();
        form.querySelector('[name="status"][value="1"]').checked = true;
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = @json(__('categories.edit'));
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(function (d) {
            var c = d.category; form.name.value = c.name;
            form.querySelectorAll('[name="status"]').forEach(function (r) { r.checked = String(r.value) === String(c.status); });
            modal.show();
        });
    }
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        var url = editingId ? base + '/' + editingId + '/update' : storeUrl;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : (j.message || @json(__('common.error'))); err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || @json(__('common.saved')), timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: @json(__('categories.confirm_delete')), icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: @json(__('common.delete')) })
            .then(function (res) { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                        if (!r.ok) { Swal.fire(@json(__('common.error')), j.message || @json(__('categories.cannot_delete')), 'error'); return; }
                        Swal.fire({ icon: 'success', title: @json(__('common.removed')), timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

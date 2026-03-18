@extends('layouts.admin')
@section('title', __('zones.title'))
@section('page-title', __('zones.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('zones.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('zones.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('zones.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('zones.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select admin-ts-select" id="filterStore" data-placeholder="{{ __('zones.filter_all_stores') }}">
                        <option value="">{{ __('zones.filter_all_stores') }}</option>
                        @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
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
                    <th>{{ __('zones.col_store') }}</th>
                    <th>{{ __('zones.col_zone') }}</th>
                    <th>{{ __('zones.col_sort') }}</th>
                    <th>{{ __('zones.col_status') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">{{ __('zones.add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <x-admin.select-search name="store_id" id="selZoneStore" :label="__('zones.label_store')" required
                        createUrl="{{ route('stores.index') }}" :createLabel="__('common.new_store')">
                        @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </x-admin.select-search>
                    <x-admin.input name="name" :label="__('zones.label_name')" required />
                    <x-admin.input name="sort_order" type="number" :label="__('zones.label_sort')" value="0" min="0" />
                    <x-admin.radio-group name="status" :label="__('zones.label_status')" :options="['1' => __('common.active'), '0' => __('common.inactive')]" selected="1" />
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
    const listUrl = @json(route('dining-zones.list'));
    const storeUrl = @json(route('dining-zones.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/dining-zone'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const selZoneStore = document.getElementById('selZoneStore');
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: { url: listUrl, data: function (d) {
            var f = document.getElementById('filterStore');
            d.store_id = f.tomselect ? f.tomselect.getValue() : f.value;
        } },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; } },
            { data: 'id' }, { data: 'store' }, { data: 'name' }, { data: 'sort_order' },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[4, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    dtProBulk({ dt: dt, bulkUrl: @json(route('dining-zones.bulkDestroy')), csrfToken: csrf, confirmMsg: @json(__('common.confirm_bulk_delete')), deleteLabel: @json(__('common.delete')) });

    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = @json(__('zones.add')); form.reset();
        form.querySelector('[name="status"][value="1"]').checked = true;
        ensureTs(selZoneStore); if (selZoneStore.tomselect && selZoneStore.options[0]) selZoneStore.tomselect.setValue(selZoneStore.options[0].value, true);
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = @json(__('zones.edit'));
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ zone: z }) => {
            form.name.value = z.name; form.sort_order.value = z.sort_order;
            form.querySelectorAll('[name="status"]').forEach(function (r) { r.checked = String(r.value) === String(z.status); });
            ensureTs(selZoneStore);
            if (selZoneStore.tomselect) selZoneStore.tomselect.setValue(String(z.store_id), true); else selZoneStore.value = z.store_id;
            modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        fetch(editingId ? base + '/' + editingId + '/update' : storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : (j.message || @json(__('common.error'))); err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || @json(__('common.saved')), timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: @json(__('zones.confirm_delete')), icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: @json(__('common.delete')) })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire(@json(__('common.error')), j.message || @json(__('common.cannot_delete')), 'error'); return; }
                        Swal.fire({ icon: 'success', title: @json(__('common.removed')), timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

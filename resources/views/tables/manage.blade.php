@extends('layouts.admin')
@section('title', __('tables.title'))
@section('page-title', __('tables.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('tables.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('tables.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('tables.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('tables.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select" id="filterZone">
                        <option value="">{{ __('tables.all_zones') }}</option>
                        @foreach ($zones as $z)
                        <option value="{{ $z->id }}">{{ $z->name }} — {{ $z->store->name ?? '—' }}</option>
                        @endforeach
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
                    <th>{{ __('tables.col_name') }}</th>
                    <th>{{ __('tables.col_store') }}</th>
                    <th>{{ __('tables.col_zone') }}</th>
                    <th>{{ __('tables.col_section') }}</th>
                    <th>{{ __('tables.col_capacity') }}</th>
                    <th>{{ __('tables.col_order') }}</th>
                    <th>{{ __('tables.col_status') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">{{ __('tables.add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="name" :label="__('tables.label_name')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="store_id" id="selTableStore" :label="__('tables.label_store')" required wrapperClass="mb-0"
                                createUrl="{{ route('stores.index') }}" :createLabel="__('common.new_store')">
                                @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('tables.label_zone') }}</label>
                            <select name="zone_id" id="selZone" class="form-select"><option value="">{{ __('common.none') }}</option></select>
                        </div>
                        <div class="col-md-4"><x-admin.input name="section" :label="__('tables.label_section')" wrapperClass="mb-0" /></div>
                        <div class="col-md-4"><x-admin.input name="capacity" :label="__('tables.label_capacity')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-4"><x-admin.input name="sort_order" type="number" :label="__('tables.label_sort')" value="0" wrapperClass="mb-0" min="0" /></div>
                        <div class="col-md-6"><x-admin.input name="status" type="number" :label="__('tables.label_status')" value="1" wrapperClass="mb-0" required /></div>
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
    const allZones = @json($zones->map(fn($z) => ['id' => $z->id, 'store_id' => $z->store_id, 'name' => $z->name]));
    const selStore = document.getElementById('selTableStore');
    function storeVal() { return selStore.tomselect ? selStore.tomselect.getValue() : selStore.value; }
    function fillZones(storeId) {
        const sel = document.getElementById('selZone');
        const v = sel.value;
        sel.innerHTML = '<option value="">' + @json(__('common.none')) + '</option>';
        allZones.filter(z => String(z.store_id) === String(storeId)).forEach(z => {
            const o = document.createElement('option');
            o.value = z.id; o.textContent = z.name; sel.appendChild(o);
        });
        if ([...sel.options].some(o => o.value === v)) sel.value = v;
    }
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    selStore.addEventListener('change', function () { fillZones(storeVal()); });
    const listUrl = @json(route('tables.list'));
    const storeUrl = @json(route('tables.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/table'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: { url: listUrl, data: function (d) { d.zone_id = document.getElementById('filterZone').value; } },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; } },
            { data: 'table_name' }, { data: 'store' }, { data: 'zone' }, { data: 'section' },
            { data: 'capacity' }, { data: 'sort_order' }, { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[6, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    dtProBulk({ dt: dt, bulkUrl: @json(route('tables.bulkDestroy')), csrfToken: csrf, confirmMsg: @json(__('common.confirm_bulk_delete')), deleteLabel: @json(__('common.delete')) });

    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = @json(__('tables.add')); form.reset();
        ensureTs(selStore); if (selStore.tomselect && selStore.options[0]) selStore.tomselect.setValue(selStore.options[0].value, true);
        form.sort_order.value = '0'; form.status.value = '1';
        fillZones(storeVal()); document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = @json(__('tables.edit')); document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ table: t }) => {
            form.name.value = t.table_name;
            ensureTs(selStore);
            if (selStore.tomselect) selStore.tomselect.setValue(String(t.store_id), true); else selStore.value = t.store_id;
            fillZones(t.store_id);
            form.zone_id.value = t.zone_id || ''; form.section.value = t.section||''; form.capacity.value = t.capacity;
            form.sort_order.value = t.sort_order; form.status.value = t.status; modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        fetch(editingId ? base + '/' + editingId + '/update' : storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || @json(__('common.saved')), timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: @json(__('tables.confirm_delete')), icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: @json(__('common.delete')) })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire(@json(__('common.error')), j.message || @json(__('common.error')), 'error'); return; }
                        Swal.fire({ icon: 'success', title: @json(__('common.removed')), timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

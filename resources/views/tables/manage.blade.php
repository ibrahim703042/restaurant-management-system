@extends('layouts.admin')
@section('title', 'Dining tables')
@section('page-title', 'Dining tables')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Tables</li>
@endsection
@section('main-section')
<x-admin.table-card title="Tables" icon="fas fa-chair">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add table</button>
    </x-slot:actions>
    <x-slot:filters>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Store</label>
                <select class="form-select form-select-sm admin-ts-select" id="filterStore" data-placeholder="All stores">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="btnApply">Apply filter</button></div>
        </div>
    </x-slot:filters>
    <thead class="table-light"><tr><th>#</th><th>Name</th><th>Store</th><th>Zone</th><th>Section</th><th>Capacity</th><th>Order</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
</x-admin.table-card>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add table</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="name" label="Table name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="store_id" id="selTableStore" label="Store" required wrapperClass="mb-0"
                                createUrl="{{ route('stores.index') }}" createLabel="New store">
                                @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Zone</label>
                            <select name="zone_id" id="selZone" class="form-select"><option value="">— None —</option></select>
                        </div>
                        <div class="col-md-4"><x-admin.input name="section" label="Section (legacy)" wrapperClass="mb-0" /></div>
                        <div class="col-md-4"><x-admin.input name="capacity" label="Capacity" wrapperClass="mb-0" required /></div>
                        <div class="col-md-4"><x-admin.input name="sort_order" type="number" label="Sort order" value="0" wrapperClass="mb-0" min="0" /></div>
                        <div class="col-md-6"><x-admin.input name="status" type="number" label="Status" value="1" wrapperClass="mb-0" required /></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
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
        sel.innerHTML = '<option value="">— None —</option>';
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
        ajax: { url: listUrl, data: function (d) {
            var f = document.getElementById('filterStore');
            d.store_id = f.tomselect ? f.tomselect.getValue() : f.value;
        } },
        columns: [
            { data: 'id' }, { data: 'table_name' }, { data: 'store' }, { data: 'zone' }, { data: 'section' },
            { data: 'capacity' }, { data: 'sort_order' }, { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[6, 'asc']]
    });
    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add table'; form.reset();
        ensureTs(selStore); if (selStore.tomselect && selStore.options[0]) selStore.tomselect.setValue(selStore.options[0].value, true);
        form.sort_order.value = '0'; form.status.value = '1';
        fillZones(storeVal()); document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit table'; document.getElementById('formErrors').classList.add('d-none');
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
                modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Saved', timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Remove table?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire('Error', j.message||'Error', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

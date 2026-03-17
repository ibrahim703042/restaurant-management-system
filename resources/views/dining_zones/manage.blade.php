@extends('layouts.admin')
@section('title', 'Dining zones')
@section('page-title', 'Dining zones')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Zones</li>
@endsection
@section('main-section')
<x-admin.table-card title="Floor zones" icon="fas fa-map-marker-alt">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add zone</button>
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
    <thead class="table-light"><tr><th>#</th><th>Store</th><th>Zone</th><th>Sort</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
</x-admin.table-card>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add zone</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <x-admin.select-search name="store_id" id="selZoneStore" label="Store" required
                        createUrl="{{ route('stores.index') }}" createLabel="New store">
                        @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </x-admin.select-search>
                    <x-admin.input name="name" label="Name" required />
                    <x-admin.input name="sort_order" type="number" label="Sort order" value="0" min="0" />
                    <x-admin.radio-group name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" selected="1" />
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
            { data: 'id' }, { data: 'store' }, { data: 'name' }, { data: 'sort_order' },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[3, 'asc']]
    });
    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add zone'; form.reset();
        form.querySelector('[name="status"][value="1"]').checked = true;
        ensureTs(selZoneStore); if (selZoneStore.tomselect && selZoneStore.options[0]) selZoneStore.tomselect.setValue(selZoneStore.options[0].value, true);
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit zone';
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
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Saved', timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Delete zone?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
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

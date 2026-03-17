@extends('layouts.admin')
@section('title', 'Dining tables')
@section('page-title', 'Dining tables')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Tables</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-chair me-2"></i>Tables</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add table</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Store</label>
                <select class="form-select form-select-sm" id="filterStore">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="btnApply">Apply filter</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle w-100" id="dt-table">
                <thead class="table-light"><tr><th>#</th><th>Name</th><th>Store</th><th>Zone</th><th>Section</th><th>Capacity</th><th>Order</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add table</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Table name</label><input name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Store</label>
                            <select name="store_id" class="form-select" required>@foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Zone</label>
                            <select name="zone_id" id="selZone" class="form-select"><option value="">— None —</option></select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Section (legacy)</label><input name="section" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Capacity</label><input name="capacity" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><input type="number" name="status" class="form-control" value="1" required></div>
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
    function fillZones(storeId) {
        const sel = document.getElementById('selZone');
        const v = sel.value;
        sel.innerHTML = '<option value="">— None —</option>';
        allZones.filter(z => String(z.store_id) === String(storeId)).forEach(z => {
            const o = document.createElement('option');
            o.value = z.id; o.textContent = z.name; sel.appendChild(o);
        });
        sel.value = v;
    }
    document.querySelector('[name="store_id"]')?.addEventListener('change', function() { fillZones(this.value); });
    const listUrl = @json(route('tables.list'));
    const storeUrl = @json(route('tables.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/table'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: listUrl, data: function (d) { d.store_id = document.getElementById('filterStore').value; } },
        columns: [
            { data: 'id' }, { data: 'table_name' }, { data: 'store' }, { data: 'zone' }, { data: 'section' },
            { data: 'capacity' }, { data: 'sort_order' }, { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[6, 'asc']], pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add table'; form.reset(); form.sort_order.value = '0'; form.status.value = '1';
        fillZones(form.store_id.value); document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit table'; document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ table: t }) => {
            form.name.value = t.table_name; form.store_id.value = t.store_id; fillZones(t.store_id);
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

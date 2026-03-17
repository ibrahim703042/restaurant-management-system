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
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="search" class="form-control" id="filterSearch" placeholder="Search…"></div>
            <div class="col-md-3">
                <select class="form-select" id="filterStore">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Name</th><th>Store</th><th>Section</th><th>Capacity</th><th>Order</th><th>Status</th><th style="width:140px">Actions</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
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
                        <div class="col-md-4"><label class="form-label">Section</label><input name="section" class="form-control"></div>
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
    const listUrl = @json(route('tables.list'));
    const storeUrl = @json(route('tables.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/table'));
    let page = 1, editingId = null;
    const tbody = document.getElementById('tbody'), pagination = document.getElementById('pagination');
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    function loadList(p = 1) {
        page = p;
        const params = new URLSearchParams({ page, search: document.getElementById('filterSearch').value, store_id: document.getElementById('filterStore').value });
        fetch(listUrl + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const tr = document.createElement('tr');
                    const sn = row.store ? row.store.name : '—';
                    tr.innerHTML = `<td>${row.id}</td><td>${escapeHtml(row.table_name)}</td><td>${escapeHtml(sn)}</td><td>${escapeHtml(row.section||'')}</td><td>${escapeHtml(row.capacity)}</td><td>${row.sort_order}</td><td>${row.status}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${row.id}">Delete</button></td>`;
                    tbody.appendChild(tr);
                });
                let h = '<ul class="pagination pagination-sm mb-0">';
                for (let i = 1; i <= d.last_page; i++) h += `<li class="page-item ${i===d.current_page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                pagination.innerHTML = d.last_page > 1 ? h : '';
                pagination.querySelectorAll('[data-page]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); loadList(+a.dataset.page); }));
                tbody.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', () => openEdit(b.dataset.id)));
                tbody.querySelectorAll('.btn-del').forEach(b => b.addEventListener('click', () => doDel(b.dataset.id)));
            });
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
    document.getElementById('btnApply').addEventListener('click', () => loadList(1));
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add table'; form.reset(); form.sort_order.value = '0'; form.status.value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit table'; document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ table: t }) => {
            form.name.value = t.table_name; form.store_id.value = t.store_id; form.section.value = t.section||''; form.capacity.value = t.capacity;
            form.sort_order.value = t.sort_order; form.status.value = t.status; modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        fetch(editingId ? base + '/' + editingId + '/update' : storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Saved', timer: 1500, showConfirmButton: false }); loadList(page);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Remove table?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire('Error', j.message||'Error', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); loadList(page);
                    });
            });
    }
    loadList(1);
})();
</script>
@endpush

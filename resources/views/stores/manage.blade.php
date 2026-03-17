@extends('layouts.admin')
@section('title', 'Stores')
@section('page-title', 'Stores')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Stores</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-store me-2"></i>Branches / stores</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add store</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="search" class="form-control" id="filterSearch" placeholder="Search…"></div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Name</th><th>Code</th><th>Phone</th><th>Primary stock</th><th>Status</th><th style="width:140px">Actions</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add store</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Code</label><input name="code" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><input type="number" name="status" class="form-control" value="1" required></div>
                        <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_primary_stock_location" id="chkPrimary" value="1" checked><label class="form-check-label" for="chkPrimary">Primary stock location</label></div></div>
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
    const listUrl = @json(route('stores.list'));
    const storeUrl = @json(route('stores.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/store'));
    let page = 1, editingId = null;
    const tbody = document.getElementById('tbody'), pagination = document.getElementById('pagination');
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    function loadList(p = 1) {
        page = p;
        fetch(listUrl + '?' + new URLSearchParams({ page, search: document.getElementById('filterSearch').value }), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.id}</td><td>${escapeHtml(row.name)}</td><td>${escapeHtml(row.code||'')}</td><td>${escapeHtml(row.phone||'')}</td><td>${row.is_primary_stock_location?'Yes':'No'}</td><td>${row.status}</td>
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
    document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); loadList(1); } });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add store'; form.reset(); form.status.value = '1'; document.getElementById('chkPrimary').checked = true;
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit store';
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ store: s }) => {
            form.name.value = s.name||''; form.code.value = s.code||''; form.phone.value = s.phone||''; form.address.value = s.address||'';
            form.notes.value = s.notes||''; form.status.value = s.status; document.getElementById('chkPrimary').checked = !!s.is_primary_stock_location; modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        if (!document.getElementById('chkPrimary').checked) fd.append('is_primary_stock_location', '0');
        const url = editingId ? base + '/' + editingId + '/update' : storeUrl;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Saved', timer: 1500, showConfirmButton: false }); loadList(page);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Remove store?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire('Error', j.message||'Cannot delete', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); loadList(page);
                    });
            });
    }
    loadList(1);
})();
</script>
@endpush

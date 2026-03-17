@extends('layouts.admin')
@section('title', 'Categories')
@section('page-title', 'Categories')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Categories</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-th me-2"></i>Menu categories</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add category</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="search" class="form-control" id="filterSearch" placeholder="Search name…"></div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Name</th><th>Status</th><th style="width:140px">Actions</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="1">Active</option><option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
    let page = 1, editingId = null;
    const tbody = document.getElementById('tbody');
    const pagination = document.getElementById('pagination');
    const modal = new bootstrap.Modal(document.getElementById('modal'));
    const form = document.getElementById('form');
    function loadList(p = 1) {
        page = p;
        const params = new URLSearchParams({ page, search: document.getElementById('filterSearch').value, status: document.getElementById('filterStatus').value });
        fetch(listUrl + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.id}</td><td>${escapeHtml(row.name)}</td><td>${row.status == 1 ? 'Active' : 'Inactive'}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${row.id}">Delete</button></td>`;
                    tbody.appendChild(tr);
                });
                let h = '<ul class="pagination pagination-sm mb-0">';
                for (let i = 1; i <= d.last_page; i++) h += `<li class="page-item ${i === d.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                h += '</ul>';
                pagination.innerHTML = d.last_page > 1 ? h : '';
                pagination.querySelectorAll('[data-page]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); loadList(+a.dataset.page); }));
                tbody.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', () => openEdit(b.dataset.id)));
                tbody.querySelectorAll('.btn-del').forEach(b => b.addEventListener('click', () => doDel(b.dataset.id)));
            });
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    document.getElementById('btnApply').addEventListener('click', () => loadList(1));
    document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); loadList(1); } });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add category'; form.reset(); form.status.value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit category';
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(({ category: c }) => {
            form.name.value = c.name; form.status.value = c.status; modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        const url = editingId ? base + '/' + editingId + '/update' : storeUrl;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : (j.message || 'Error'); err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || 'Saved', timer: 1500, showConfirmButton: false }); loadList(page);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Delete category?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async r => { const j = await r.json().catch(() => ({}));
                        if (!r.ok) { Swal.fire('Error', j.message || 'Cannot delete', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); loadList(page);
                    });
            });
    }
    loadList(1);
})();
</script>
@endpush

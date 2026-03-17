@extends('layouts.admin')
@section('title', 'Products')
@section('page-title', 'Products')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Products</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Menu products</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add product</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="search" class="form-control" id="filterSearch" placeholder="Search…"></div>
            <div class="col-md-2">
                <select class="form-select" id="filterCat"><option value="">All categories</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStore"><option value="">All stores</option>@foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Image</th><th>Name</th><th>Price</th><th>Category</th><th>Store</th><th>Status</th><th style="width:140px">Actions</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form" enctype="multipart/form-data">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="product_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Price</label><input type="number" step="0.01" min="0" name="price" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Store</label>
                            <select name="store_id" class="form-select" required>@foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Status</label><input type="number" name="status" class="form-control" value="1" required></div>
                        <div class="col-md-6"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
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
    const listUrl = @json(route('products.list'));
    const storeUrl = @json(route('products.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/product'));
    const storageBase = @json(asset('storage'));
    let page = 1, editingId = null;
    const tbody = document.getElementById('tbody'), pagination = document.getElementById('pagination');
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    function imgUrl(path) {
        if (!path || path === 'products/default.png') return '<span class="text-muted">—</span>';
        return '<img src="' + storageBase + '/' + path.replace(/^\\//,'') + '" width="36" height="36" class="rounded object-fit-cover" alt="">';
    }
    function loadList(p = 1) {
        page = p;
        const params = new URLSearchParams({ page, search: document.getElementById('filterSearch').value, category_id: document.getElementById('filterCat').value, store_id: document.getElementById('filterStore').value });
        fetch(listUrl + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.id}</td><td>${imgUrl(row.image)}</td><td>${escapeHtml(row.product_name)}</td><td>${Number(row.price).toFixed(2)}</td>
                        <td>${row.category ? escapeHtml(row.category.name) : '—'}</td><td>${row.store ? escapeHtml(row.store.name) : '—'}</td><td>${row.status}</td>
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
        editingId = null; document.getElementById('modalTitle').textContent = 'Add product'; form.reset(); form.status.value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit product'; document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ product: p }) => {
            form.product_name.value = p.product_name; form.price.value = p.price; form.category_id.value = p.category_id; form.store_id.value = p.store_id;
            form.status.value = p.status; form.description.value = p.description||''; form.image.value = ''; modal.show();
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
        Swal.fire({ title: 'Delete product?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
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

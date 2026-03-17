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
        <h3 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Shared menu (all branches)</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add product</button>
    </div>
    <div class="card-body">
        <p class="text-muted small">Stock per store is managed under <strong>Inventory</strong>.</p>
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Category</label>
                <select class="form-select form-select-sm" id="filterCat"><option value="">All categories</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="btnApply">Apply filter</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle w-100" id="dt-table">
                <thead class="table-light"><tr><th>#</th><th>Image</th><th>Name</th><th>Price</th><th>Category</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
            </table>
        </div>
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
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal'));
    const form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: listUrl, data: function (d) { d.category_id = document.getElementById('filterCat').value; } },
        columns: [
            { data: 'id' },
            { data: 'image_html', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name' }, { data: 'price' }, { data: 'category' },
            { data: 'status' }, { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[2, 'asc']], pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add product'; form.reset(); form.status.value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit product'; document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(function (d) {
            var p = d.product; form.product_name.value = p.product_name; form.price.value = p.price; form.category_id.value = p.category_id;
            form.status.value = p.status; form.description.value = p.description || ''; form.querySelector('[name=image]').value = ''; modal.show();
        });
    }
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        fetch(editingId ? base + '/' + editingId + '/update' : storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || 'Saved', timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Delete product?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(function (res) { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                        if (!r.ok) { Swal.fire('Error', j.message || 'Error', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

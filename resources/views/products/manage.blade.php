@extends('layouts.admin')
@section('title', 'Products')
@section('page-title', 'Products')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Products</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions title="Products">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>Add product
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="Search products…">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select admin-ts-select" id="filterCat" data-placeholder="All categories">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <button type="button" class="btn btn-dt-pro-outline btn-sm px-3" id="btnApply">
                        <i class="fas fa-sliders-h me-1"></i>Filter
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th class="text-end">Price</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="text-end pe-4" style="width:160px">Actions</th>
                </tr>
            </thead>
        </table>
        <x-slot:footer>
            <div class="small text-muted">Shared menu (all branches). Stock per store is managed under <strong>Inventory</strong>.</div>
        </x-slot:footer>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">Add product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form" enctype="multipart/form-data">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="product_name" label="Name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="price" type="number" label="Price" wrapperClass="mb-0" required step="0.01" min="0" /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="category_id" id="selCategory" label="Category" required wrapperClass="mb-0"
                                createUrl="{{ route('categories.index') }}" createLabel="New category">
                                @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6"><x-admin.input name="status" type="number" label="Status" value="1" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="col-12"><x-admin.textarea name="description" label="Description" rows="2" wrapperClass="mb-0" /></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">Save</button>
                </div>
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
    const selCategory = document.getElementById('selCategory');
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: { url: listUrl, data: function (d) {
            var f = document.getElementById('filterCat');
            d.category_id = f.tomselect ? f.tomselect.getValue() : f.value;
        } },
        columns: [
            { data: 'id' },
            { data: 'image_html', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name' }, { data: 'price' }, { data: 'category' },
            { data: 'status' }, { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[2, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add product'; form.reset();
        ensureTs(selCategory);
        var fc = selCategory.querySelector('option[value]');
        if (selCategory.tomselect && fc) selCategory.tomselect.setValue(fc.value, true);
        form.querySelector('[name=status]').value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit product'; document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(function (d) {
            var p = d.product;
            form.product_name.value = p.product_name; form.price.value = p.price;
            ensureTs(selCategory);
            if (selCategory.tomselect) selCategory.tomselect.setValue(String(p.category_id), true);
            else selCategory.value = p.category_id;
            form.status.value = p.status; form.description.value = p.description || ''; form.querySelector('[name=image]').value = '';
            modal.show();
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

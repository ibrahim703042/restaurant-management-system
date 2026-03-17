@extends('layouts.admin')
@section('title', 'Dining zones')
@section('page-title', 'Dining zones')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Zones</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Floor zones</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add zone</button>
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
                <thead class="table-light"><tr><th>#</th><th>Store</th><th>Zone</th><th>Sort</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add zone</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-2"><label class="form-label">Store</label>
                        <select name="store_id" class="form-select" required>@foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                    </div>
                    <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select>
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
    const listUrl = @json(route('dining-zones.list'));
    const storeUrl = @json(route('dining-zones.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/dining-zone'));
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: listUrl, data: function (d) { d.store_id = document.getElementById('filterStore').value; } },
        columns: [
            { data: 'id' }, { data: 'store' }, { data: 'name' }, { data: 'sort_order' },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[3, 'asc']], pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
    document.getElementById('btnApply').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add zone'; form.reset(); form.status.value = '1';
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit zone';
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(({ zone: z }) => {
            form.store_id.value = z.store_id; form.name.value = z.name; form.sort_order.value = z.sort_order; form.status.value = z.status; modal.show();
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
                        if (!r.ok) { Swal.fire('Error', j.message||'Cannot delete', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

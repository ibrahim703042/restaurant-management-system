@extends('layouts.admin')
@section('title', 'Stores')
@section('page-title', 'Stores')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Stores</li>
@endsection
@section('main-section')
<x-admin.table-card title="Branches / stores" icon="fas fa-store">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add store</button>
    </x-slot:actions>
    <thead class="table-light"><tr><th>#</th><th>Name</th><th>Code</th><th>Phone</th><th>Primary stock</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
</x-admin.table-card>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add store</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="name" label="Name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="code" label="Code" wrapperClass="mb-0" /></div>
                        <div class="col-md-6"><x-admin.input name="phone" label="Phone" wrapperClass="mb-0" /></div>
                        <div class="col-md-6"><x-admin.input name="status" type="number" label="Status" value="1" wrapperClass="mb-0" required /></div>
                        <div class="col-12"><x-admin.input name="address" label="Address" wrapperClass="mb-0" /></div>
                        <div class="col-12"><x-admin.textarea name="notes" label="Notes" rows="2" wrapperClass="mb-0" /></div>
                        <div class="col-12"><x-admin.checkbox name="is_primary_stock_location" value="1" label="Primary stock location" :checked="true" id="chkPrimary" /></div>
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
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const dt = $('#dt-table').DataTable({
        serverSide: true, ajax: { url: listUrl },
        columns: [
            { data: 'id' }, { data: 'name' }, { data: 'code' }, { data: 'phone' },
            { data: 'primary_html', orderable: false, searchable: false },
            { data: 'status_html', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[1, 'asc']]
    });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add store'; form.reset(); form.status.value = '1'; document.getElementById('chkPrimary').checked = true;
        document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit store';
        document.getElementById('formErrors').classList.add('d-none');
        fetch(base + '/' + id + '/json').then(r => r.json()).then(function (d) {
            var s = d.store; form.name.value = s.name || ''; form.code.value = s.code || ''; form.phone.value = s.phone || ''; form.address.value = s.address || '';
            form.notes.value = s.notes || ''; form.status.value = s.status; document.getElementById('chkPrimary').checked = !!s.is_primary_stock_location; modal.show();
        });
    }
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        if (!document.getElementById('chkPrimary').checked) fd.append('is_primary_stock_location', '0');
        var url = editingId ? base + '/' + editingId + '/update' : storeUrl;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || 'Saved', timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Remove store?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(function (res) { if (!res.isConfirmed) return;
                fetch(base + '/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                        if (!r.ok) { Swal.fire('Error', j.message || 'Cannot delete', 'error'); return; }
                        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                    });
            });
    }
})();
</script>
@endpush

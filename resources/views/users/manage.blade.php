@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Users</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions title="Users">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>Add user
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="Search users…">
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Employee</th>
                    <th class="text-end pe-4" style="width:180px">Actions</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">Add user</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="name" label="Name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="email" type="email" label="Email" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6" id="pwWrap1"><x-admin.input name="password" type="password" label="Password" id="inpPassword" wrapperClass="mb-0" /></div>
                        <div class="col-md-6" id="pwWrap2"><x-admin.input name="password_confirmation" type="password" label="Confirm password" wrapperClass="mb-0" /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="role" label="Role" wrapperClass="mb-0" required>
                                @foreach ($roles as $rn => $rv)<option value="{{ $rn }}">{{ $rn }}</option>@endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Link employee (optional)</label>
                            <select name="employee_id" id="selEmployee" class="form-select"><option value="">— None —</option></select>
                        </div>
                        <div class="col-12">
                            <x-admin.select-search name="store_ids[]" id="selStores" label="Stores (POS / API)" multiple placeholder="All stores if empty…" wrapperClass="mb-1">
                                @foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
                            </x-admin.select-search>
                            <p class="small text-muted mb-0">Leave empty = access <strong>all</strong> active stores.</p>
                        </div>
                        <p class="small text-muted mb-0" id="editPwHint">Leave password blank to keep current.</p>
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
    const listUrl = @json(route('users.list'));
    const storeUrl = @json(route('users.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/user'));
    const authId = {{ auth()->id() }};
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const selEmployee = document.getElementById('selEmployee');
    const selStores = document.getElementById('selStores');
    function clearStores() {
        Array.from(selStores.options).forEach(function (o) { o.selected = false; });
        if (selStores.tomselect) selStores.tomselect.clear(true);
    }
    function setStores(ids) { var s = new Set((ids || []).map(Number)); Array.from(selStores.options).forEach(function (o) { o.selected = s.has(+o.value); }); }
    function loadEmployees(userId) {
        var url = userId ? (base + '/' + userId + '/employees-json') : (base + '/employees-json');
        return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) {
            selEmployee.innerHTML = '<option value="">— None —</option>';
            (d.employees || []).forEach(function (e) {
                var o = document.createElement('option');
                o.value = e.id; o.textContent = e.first_name + ' ' + e.last_name; selEmployee.appendChild(o);
            });
        });
    }
    const dt = $('#dt-table').DataTable({
        serverSide: true, ajax: { url: listUrl },
        columns: [
            { data: 'id' }, { data: 'name' }, { data: 'email' }, { data: 'role' }, { data: 'employee' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[1, 'asc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { doDel($(this).data('id')); });
    function tsClear(sel) { if (sel && sel.tomselect) sel.tomselect.clear(true); }
    document.getElementById('btnAdd').addEventListener('click', function () {
        editingId = null; document.getElementById('modalTitle').textContent = 'Add user'; form.reset();
        ensureTs(form.role); ensureTs(selStores);
        tsClear(form.role); tsClear(selStores);
        var ro = form.role.querySelector('option');
        if (form.role.tomselect && ro) form.role.tomselect.setValue(ro.value, true);
        document.getElementById('pwWrap1').classList.remove('d-none'); document.getElementById('pwWrap2').classList.remove('d-none');
        document.getElementById('inpPassword').required = true; document.getElementById('editPwHint').classList.add('d-none');
        document.getElementById('formErrors').classList.add('d-none');
        clearStores(); loadEmployees(null).then(function () { modal.show(); });
    });
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit user';
        document.getElementById('inpPassword').required = false; document.getElementById('editPwHint').classList.remove('d-none');
        document.getElementById('formErrors').classList.add('d-none');
        Promise.all([fetch(base + '/' + id + '/json').then(function (r) { return r.json(); }), loadEmployees(id)]).then(function (arr) {
            var u = arr[0].user;
            form.name.value = u.name; form.email.value = u.email; form.password.value = ''; form.password_confirmation.value = '';
            ensureTs(form.role); ensureTs(selStores);
            if (form.role.tomselect) form.role.tomselect.setValue(u.role || '', true); else form.role.value = u.role || '';
            selEmployee.value = u.employee_id || ''; setStores(u.store_ids);
            if (selStores.tomselect) selStores.tomselect.setValue((u.store_ids || []).map(String), true);
            modal.show();
        });
    }
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        if (editingId) fd.append('_method', 'PUT');
        var url = editingId ? base + '/' + editingId : storeUrl;
        if (editingId && !fd.get('password')) { fd.delete('password'); fd.delete('password_confirmation'); }
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || 'Saved', timer: 1500, showConfirmButton: false }); dt.ajax.reload(null, false);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Delete user?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
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

@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Users</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-users-cog me-2"></i>App users</h3>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add user</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="search" class="form-control" id="filterSearch" placeholder="Search name, email…"></div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Employee</th><th style="width:160px">Actions</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add user</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6" id="pwWrap1"><label class="form-label">Password</label><input type="password" name="password" id="inpPassword" class="form-control"></div>
                        <div class="col-md-6" id="pwWrap2"><label class="form-label">Confirm password</label><input type="password" name="password_confirmation" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Role</label>
                            <select name="role" class="form-select" required>@foreach ($roles as $rn => $rv)<option value="{{ $rn }}">{{ $rn }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Link employee (optional)</label>
                            <select name="employee_id" id="selEmployee" class="form-select"><option value="">— None —</option></select>
                        </div>
                        <p class="small text-muted mb-0" id="editPwHint">Leave password blank to keep current.</p>
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
    const listUrl = @json(route('users.list'));
    const storeUrl = @json(route('users.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/user'));
    const authId = {{ auth()->id() }};
    let page = 1, editingId = null;
    const tbody = document.getElementById('tbody'), pagination = document.getElementById('pagination');
    const modal = new bootstrap.Modal(document.getElementById('modal')), form = document.getElementById('form');
    const selEmployee = document.getElementById('selEmployee');
    function loadEmployees(userId) {
        const url = userId ? (base + '/' + userId + '/employees-json') : (base + '/employees-json');
        return fetch(url, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(d => {
            selEmployee.innerHTML = '<option value="">— None —</option>';
            (d.employees || []).forEach(e => {
                const o = document.createElement('option');
                o.value = e.id; o.textContent = e.first_name + ' ' + e.last_name; selEmployee.appendChild(o);
            });
        });
    }
    function loadList(p = 1) {
        page = p;
        fetch(listUrl + '?' + new URLSearchParams({ page, search: document.getElementById('filterSearch').value }), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const role = (row.role_names && row.role_names[0]) ? row.role_names[0] : '—';
                    const en = row.employee ? (row.employee.first_name + ' ' + row.employee.last_name) : '—';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.id}</td><td>${escapeHtml(row.name)}</td><td>${escapeHtml(row.email)}</td><td>${escapeHtml(role)}</td><td>${escapeHtml(en)}</td>
                        <td>${row.id == authId ? '<span class="text-muted small">You</span>' : `<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${row.id}">Delete</button>`}</td>`;
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
        editingId = null; document.getElementById('modalTitle').textContent = 'Add user'; form.reset();
        document.getElementById('pwWrap1').classList.remove('d-none'); document.getElementById('pwWrap2').classList.remove('d-none');
        document.getElementById('inpPassword').required = true; document.getElementById('editPwHint').classList.add('d-none');
        document.getElementById('formErrors').classList.add('d-none');
        loadEmployees(null).then(() => modal.show());
    });
    function openEdit(id) {
        editingId = id; document.getElementById('modalTitle').textContent = 'Edit user';
        document.getElementById('inpPassword').required = false; document.getElementById('editPwHint').classList.remove('d-none');
        document.getElementById('formErrors').classList.add('d-none');
        Promise.all([fetch(base + '/' + id + '/json').then(r => r.json()), loadEmployees(id)]).then(([data]) => {
            const u = data.user;
            form.name.value = u.name; form.email.value = u.email; form.role.value = u.role || ''; form.password.value = ''; form.password_confirmation.value = '';
            selEmployee.value = u.employee_id || ''; modal.show();
        });
    }
    form.addEventListener('submit', ev => {
        ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
        const fd = new FormData(form);
        if (editingId) fd.append('_method', 'PUT');
        const url = editingId ? base + '/' + editingId : storeUrl;
        if (editingId && !fd.get('password')) { fd.delete('password'); fd.delete('password_confirmation'); }
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async r => { const j = await r.json().catch(() => ({}));
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Saved', timer: 1500, showConfirmButton: false }); loadList(page);
            });
    });
    function doDel(id) {
        Swal.fire({ title: 'Delete user?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
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

@extends('layouts.admin')

@section('title', 'Employees')
@section('page-title', 'Employees')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Employees</li>
@endsection

@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-users me-2"></i>Staff directory</h3>
        <button type="button" class="btn btn-primary" id="btnAddEmployee"><i class="fas fa-plus me-1"></i>Add employee</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" class="form-control" id="filterSearch" placeholder="Search name, email, phone…">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterPosition">
                    <option value="">All positions</option>
                    @foreach ($positions as $p)
                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" id="btnApplyFilter">Apply</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Position</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeeTableBody"></tbody>
            </table>
        </div>
        <nav id="employeePagination" class="mt-2"></nav>
    </div>
</div>

<div class="modal fade" id="employeeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalTitle">Add employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="employeeForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="employeeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">First name</label><input name="fname" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Last name</label><input name="lname" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Birthday</label><input type="date" name="birthdate" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Position</label>
                            <select name="position_id" class="form-select" required>
                                @foreach ($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="col-md-6"><label class="form-label">Mother name</label><input name="mother" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Father name</label><input name="father" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Country</label><input name="country" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">City</label><input name="city" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control" required></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="employeeFormSubmit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const listUrl = @json(route('employees.list'));
    const storeUrl = @json(route('employees.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let currentPage = 1;
    let editingId = null;

    const tbody = document.getElementById('employeeTableBody');
    const pagination = document.getElementById('employeePagination');
    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    const form = document.getElementById('employeeForm');

    function loadList(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({
            page,
            search: document.getElementById('filterSearch').value,
            position_id: document.getElementById('filterPosition').value,
        });
        fetch(listUrl + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                tbody.innerHTML = '';
                (d.data || []).forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${escapeHtml(row.first_name)} ${escapeHtml(row.last_name)}</td>
                        <td>${escapeHtml(row.email)}</td>
                        <td>${escapeHtml(row.phone || '')}</td>
                        <td>${row.position ? escapeHtml(row.position.title) : '—'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}">Edit</button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${row.id}">Delete</button>
                        </td>`;
                    tbody.appendChild(tr);
                });
                let pagHtml = '<ul class="pagination pagination-sm mb-0">';
                for (let p = 1; p <= d.last_page; p++) {
                    pagHtml += `<li class="page-item ${p === d.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                }
                pagHtml += '</ul>';
                pagination.innerHTML = d.last_page > 1 ? pagHtml : '';
                pagination.querySelectorAll('[data-page]').forEach(a => {
                    a.addEventListener('click', e => { e.preventDefault(); loadList(+a.dataset.page); });
                });
                tbody.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', () => openEdit(btn.dataset.id)));
                tbody.querySelectorAll('.btn-del').forEach(btn => btn.addEventListener('click', () => confirmDelete(btn.dataset.id)));
            });
    }

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    document.getElementById('btnApplyFilter').addEventListener('click', () => loadList(1));
    document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); loadList(1); } });

    document.getElementById('btnAddEmployee').addEventListener('click', () => {
        editingId = null;
        document.getElementById('employeeModalTitle').textContent = 'Add employee';
        form.reset();
        document.getElementById('employeeFormErrors').classList.add('d-none');
        modal.show();
    });

    function openEdit(id) {
        editingId = id;
        document.getElementById('employeeModalTitle').textContent = 'Edit employee';
        document.getElementById('employeeFormErrors').classList.add('d-none');
        fetch(`{{ url('/employee') }}/${id}/json`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(({ employee: e }) => {
                form.fname.value = e.first_name;
                form.lname.value = e.last_name;
                form.email.value = e.email;
                form.phone.value = e.phone;
                form.gender.value = e.gender;
                form.birthdate.value = (e.birthday || '').substring(0, 10);
                form.position_id.value = e.position_id;
                form.mother.value = e.mother_name;
                form.father.value = e.father_name;
                form.country.value = e.country;
                form.city.value = e.city;
                form.address.value = e.address;
                form.image.value = '';
                modal.show();
            });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        const errBox = document.getElementById('employeeFormErrors');
        errBox.classList.add('d-none');
        const fd = new FormData(form);
        const url = editingId ? `{{ url('/employee') }}/${editingId}/update` : storeUrl;
        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
            .then(async r => {
                const j = await r.json().catch(() => ({}));
                if (!r.ok) {
                    let msg = j.message || 'Error';
                    if (j.errors) {
                        msg = Object.values(j.errors).flat().join('<br>');
                    }
                    errBox.innerHTML = msg;
                    errBox.classList.remove('d-none');
                    return;
                }
                modal.hide();
                Swal.fire({ icon: 'success', title: j.message || 'Saved', timer: 1500, showConfirmButton: false });
                loadList(currentPage);
            });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Remove employee?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete',
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/employee') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            })
                .then(r => r.json())
                .then(() => { Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); loadList(currentPage); });
        });
    }

    loadList(1);
})();
</script>
@endpush

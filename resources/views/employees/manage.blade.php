@extends('layouts.admin')

@section('title', 'Employees')
@section('page-title', 'Employees')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Employees</li>
@endsection

@section('main-section')
<x-admin.table-card title="Staff directory" icon="fas fa-users">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAddEmployee"><i class="fas fa-plus me-1"></i>Add employee</button>
    </x-slot:actions>
    <x-slot:filters>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Position</label>
                <select class="form-select form-select-sm admin-ts-select" id="filterPosition" data-placeholder="All positions">
                    <option value="">All positions</option>
                    @foreach ($positions as $p)
                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="btnApplyFilter">Apply filter</button></div>
        </div>
    </x-slot:filters>
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Position</th>
            <th style="width:160px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

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
                        <div class="col-md-6"><x-admin.input name="fname" label="First name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="lname" label="Last name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="email" type="email" label="Email" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="phone" label="Phone" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.radio-group name="gender" label="Gender" :options="['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']" selected="Male" />
                        </div>
                        <div class="col-md-6"><x-admin.input name="birthdate" type="date" label="Birthday" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="position_id" id="selEmpPosition" label="Position" required wrapperClass="mb-0"
                                createUrl="{{ route('positions.index') }}" createLabel="New position">
                                @foreach ($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Photo</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="col-md-6"><x-admin.input name="mother" label="Mother name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="father" label="Father name" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="country" label="Country" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="city" label="City" wrapperClass="mb-0" required /></div>
                        <div class="col-12"><x-admin.input name="address" label="Address" wrapperClass="mb-0" required /></div>
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
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    const form = document.getElementById('employeeForm');
    const selPos = document.getElementById('selEmpPosition');
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: { url: listUrl, data: function (d) {
            var f = document.getElementById('filterPosition');
            d.position_id = f.tomselect ? f.tomselect.getValue() : f.value;
        } },
        columns: [
            { data: 'id' }, { data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'position' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']]
    });
    document.getElementById('btnApplyFilter').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { confirmDelete($(this).data('id')); });

    document.getElementById('btnAddEmployee').addEventListener('click', () => {
        editingId = null;
        document.getElementById('employeeModalTitle').textContent = 'Add employee';
        form.reset();
        form.querySelector('[name="gender"][value="Male"]').checked = true;
        ensureTs(selPos);
        if (selPos.tomselect && selPos.options[0]) selPos.tomselect.setValue(selPos.options[0].value, true);
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
                form.querySelectorAll('[name="gender"]').forEach(function (r) { r.checked = r.value === e.gender; });
                form.birthdate.value = (e.birthday || '').substring(0, 10);
                ensureTs(selPos);
                if (selPos.tomselect) selPos.tomselect.setValue(String(e.position_id), true);
                else selPos.value = e.position_id;
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
                dt.ajax.reload(null, false);
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
                .then(() => { Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false); });
        });
    }
})();
</script>
@endpush

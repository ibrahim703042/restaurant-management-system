@extends('layouts.admin')
@section('title', 'Employee leaves')
@section('page-title', 'Employee leaves')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Leaves</li>
@endsection

@section('main-section')
<x-admin.table-card title="Leave requests" icon="fas fa-plane-departure">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add record</button>
    </x-slot:actions>
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Type</th>
            <th>Dates</th>
            <th>Status</th>
            <th>Approver</th>
            <th style="width:280px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMain">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <input type="text" name="leave_type" class="form-control" placeholder="Annual, sick…" required>
                        </div>
                        <div class="col-md-6"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">End</label><input type="date" name="end_date" class="form-control" required></div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label">Admin note</label><textarea name="admin_note" class="form-control" rows="2"></textarea></div>
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
    const listUrl = @json(route('employee-leaves.list'));
    const storeUrl = @json(route('employee-leaves.store'));
    const base = @json(route('employee-leaves.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modalForm'));
    const form = document.getElementById('formMain');
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: listUrl,
        columns: [
            { data: 'id' }, { data: 'employee' }, { data: 'leave_type' }, { data: 'range' }, { data: 'status' }, { data: 'approver' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']]
    });
    function showErrors(err) {
        const el = document.getElementById('formErrors');
        if (!err || !Object.keys(err).length) { el.classList.add('d-none'); return; }
        el.innerHTML = Object.values(err).flat().map(e => '<div>' + e + '</div>').join('');
        el.classList.remove('d-none');
    }
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null;
        document.getElementById('modalTitle').textContent = 'Add leave';
        form.reset();
        showErrors({});
        modal.show();
    });
    $('#dt-table tbody').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        editingId = id;
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(r => r.json()).then(d => {
            const L = d.leave;
            form.employee_id.value = L.employee_id;
            form.leave_type.value = L.leave_type;
            form.start_date.value = (L.start_date || '').slice(0, 10);
            form.end_date.value = (L.end_date || '').slice(0, 10);
            form.status.value = L.status;
            form.reason.value = L.reason || '';
            form.admin_note.value = L.admin_note || '';
            document.getElementById('modalTitle').textContent = 'Edit leave';
            showErrors({});
            modal.show();
        });
    });
    $('#dt-table tbody').on('click', '.btn-approve, .btn-reject', function () {
        const id = $(this).data('id');
        const decision = $(this).hasClass('btn-approve') ? 'approved' : 'rejected';
        const fd = new FormData();
        fd.append('decision', decision);
        fd.append('_token', csrf);
        fetch(base + '/' + id + '/decide', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
        .then(() => dt.ajax.reload());
    });
    $('#dt-table tbody').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        if (!confirm('Delete?')) return;
        fetch(base + '/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(() => dt.ajax.reload());
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        let url = storeUrl;
        if (editingId) url = base + '/' + editingId + '/update';
        fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json().then(j => ({ ok: r.ok, j })))
        .then(({ ok, j }) => {
            if (ok) { modal.hide(); dt.ajax.reload(); }
            else showErrors(j.errors || { _: [j.message || 'Error'] });
        });
    });
})();
</script>
@endpush

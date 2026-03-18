@extends('layouts.admin')
@section('title', 'Work shifts')
@section('page-title', 'Work shifts')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Work shifts</li>
@endsection

@section('main-section')
<x-admin.table-card title="Scheduled shifts" icon="fas fa-calendar-alt">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add shift</button>
    </x-slot:actions>
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Time</th>
            <th>Employee</th>
            <th>Store</th>
            <th>Status</th>
            <th style="width:180px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add shift</h5>
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
                            <label class="form-label">Store</label>
                            <select name="store_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="shift_date" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Start</label><input type="time" name="starts_at" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">End</label><input type="time" name="ends_at" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Break (min)</label><input type="number" name="break_minutes" class="form-control" value="0" min="0"></div>
                        <div class="col-md-8">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No show</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
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
    const listUrl = @json(route('work-shifts.list'));
    const storeUrl = @json(route('work-shifts.store'));
    const wsBase = @json(route('work-shifts.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modalForm'));
    const form = document.getElementById('formMain');
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: listUrl,
        columns: [
            { data: 'id' }, { data: 'shift_date' }, { data: 'time' }, { data: 'employee' }, { data: 'store' }, { data: 'status' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[1, 'desc']]
    });
    function showErrors(err) {
        const el = document.getElementById('formErrors');
        if (!err || !Object.keys(err).length) { el.classList.add('d-none'); return; }
        el.innerHTML = Object.values(err).flat().map(e => '<div>' + e + '</div>').join('');
        el.classList.remove('d-none');
    }
    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null;
        document.getElementById('modalTitle').textContent = 'Add shift';
        form.reset();
        showErrors({});
        modal.show();
    });
    $('#dt-table tbody').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        editingId = id;
        fetch(wsBase + '/' + id + '/json', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(r => r.json()).then(d => {
            const s = d.shift;
            form.employee_id.value = s.employee_id;
            form.store_id.value = s.store_id || '';
            form.shift_date.value = (s.shift_date || '').slice(0, 10);
            form.starts_at.value = (s.starts_at || '').slice(0, 5);
            form.ends_at.value = (s.ends_at || '').slice(0, 5);
            form.break_minutes.value = s.break_minutes || 0;
            form.status.value = s.status;
            form.notes.value = s.notes || '';
            document.getElementById('modalTitle').textContent = 'Edit shift';
            showErrors({});
            modal.show();
        });
    });
    $('#dt-table tbody').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        if (!confirm('Delete this shift?')) return;
        fetch(wsBase + '/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json()).then(() => dt.ajax.reload());
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        let url = storeUrl, method = 'POST';
        if (editingId) {
            url = wsBase + '/' + editingId + '/update';
            method = 'POST';
        }
        fetch(url, { method, body: fd, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json().then(j => ({ ok: r.ok, j })))
        .then(({ ok, j }) => {
            if (ok) { modal.hide(); dt.ajax.reload(); }
            else showErrors(j.errors || { _: [j.message || 'Error'] });
        });
    });
})();
</script>
@endpush

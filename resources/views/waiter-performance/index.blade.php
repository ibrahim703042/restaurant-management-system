@extends('layouts.admin')
@section('title', 'Waiter performance')
@section('page-title', 'Waiter performance')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Performance</li>
@endsection

@section('main-section')
<x-admin.table-card title="Performance reviews" icon="fas fa-star">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>Add review</button>
    </x-slot:actions>
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Period</th>
            <th>Rating</th>
            <th>Tables</th>
            <th>Sales</th>
            <th style="width:160px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Performance</h5>
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
                            <label class="form-label">Rating (1–5)</label>
                            <select name="rating" class="form-select">
                                @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @if($i===3) selected @endif>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Period start</label><input type="date" name="period_start" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Period end</label><input type="date" name="period_end" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Tables served</label><input type="number" name="tables_served" class="form-control" min="0"></div>
                        <div class="col-md-6"><label class="form-label">Sales total</label><input type="number" step="0.01" name="sales_total" class="form-control" min="0"></div>
                        <div class="col-12"><label class="form-label">Comment</label><textarea name="comment" class="form-control" rows="3"></textarea></div>
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
    const listUrl = @json(route('waiter-performance.list'));
    const storeUrl = @json(route('waiter-performance.store'));
    const base = @json(route('waiter-performance.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('modalForm'));
    const form = document.getElementById('formMain');
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: listUrl,
        columns: [
            { data: 'id' }, { data: 'employee' }, { data: 'period' }, { data: 'rating' }, { data: 'tables' }, { data: 'sales' },
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
        document.getElementById('modalTitle').textContent = 'Add review';
        form.reset();
        form.rating.value = '3';
        showErrors({});
        modal.show();
    });
    $('#dt-table tbody').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        editingId = id;
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(r => r.json()).then(d => {
            const P = d.performance;
            form.employee_id.value = P.employee_id;
            form.period_start.value = (P.period_start || '').slice(0, 10);
            form.period_end.value = (P.period_end || '').slice(0, 10);
            form.rating.value = P.rating;
            form.tables_served.value = P.tables_served ?? '';
            form.sales_total.value = P.sales_total ?? '';
            form.comment.value = P.comment || '';
            document.getElementById('modalTitle').textContent = 'Edit review';
            showErrors({});
            modal.show();
        });
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

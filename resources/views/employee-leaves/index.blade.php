@extends('layouts.admin')
@section('title', __('leaves.title'))
@section('page-title', __('leaves.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('leaves.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('leaves.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('leaves.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('leaves.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button class="btn btn-danger btn-sm d-none" id="btnBulkDel">
                        <i class="fas fa-trash me-1"></i>{{ __('common.delete_selected') }}
                        <span class="dt-pro-bulk-count badge bg-white text-danger ms-1"></span>
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:42px"><input type="checkbox" class="form-check-input" id="dtCheckAll"></th>
                    <th>{{ __('leaves.col_employee') }}</th>
                    <th>{{ __('leaves.col_type') }}</th>
                    <th>{{ __('leaves.col_dates') }}</th>
                    <th>{{ __('leaves.col_status') }}</th>
                    <th>{{ __('leaves.col_approver') }}</th>
                    <th class="text-end pe-4" style="width:280px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">{{ __('leaves.add') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMain">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('leaves.label_employee') }}</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('leaves.label_type') }}</label>
                            <input type="text" name="leave_type" class="form-control" placeholder="{{ __('leaves.label_type_placeholder') }}" required>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('leaves.label_start') }}</label><input type="date" name="start_date" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('leaves.label_end') }}</label><input type="date" name="end_date" class="form-control" required></div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('leaves.label_status') }}</label>
                            <select name="status" class="form-select">
                                <option value="pending">{{ __('leaves.status_pending') }}</option>
                                <option value="approved">{{ __('leaves.status_approved') }}</option>
                                <option value="rejected">{{ __('leaves.status_rejected') }}</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label fw-semibold">{{ __('leaves.label_reason') }}</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label fw-semibold">{{ __('leaves.label_admin_note') }}</label><textarea name="admin_note" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('common.save') }}</button>
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
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; }
            },
            { data: 'employee' }, { data: 'leave_type' }, { data: 'range' }, { data: 'status' }, { data: 'approver' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']],
        dom: 'rtip'
    });

    var searchInput = document.getElementById('dtSearchInput');
    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { dt.search(searchInput.value).draw(); }, 350);
    });

    dtProBulk({
        dt: dt,
        bulkUrl: @json(route('employee-leaves.bulkDestroy')),
        csrfToken: csrf,
        confirmMsg: @json(__('common.confirm_bulk_delete')),
        deleteLabel: @json(__('common.delete'))
    });

    function showErrors(err) {
        const el = document.getElementById('formErrors');
        if (!err || !Object.keys(err).length) { el.classList.add('d-none'); return; }
        el.innerHTML = Object.values(err).flat().map(e => '<div>' + e + '</div>').join('');
        el.classList.remove('d-none');
    }

    document.getElementById('btnAdd').addEventListener('click', () => {
        editingId = null;
        document.getElementById('modalTitle').textContent = @json(__('leaves.add'));
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
            document.getElementById('modalTitle').textContent = @json(__('leaves.edit'));
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
        .then(() => dt.ajax.reload(null, false));
    });

    $('#dt-table tbody').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: @json(__('leaves.confirm_delete')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: @json(__('common.delete'))
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(base + '/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(() => dt.ajax.reload(null, false));
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        let url = storeUrl;
        if (editingId) url = base + '/' + editingId + '/update';
        fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json().then(j => ({ ok: r.ok, j })))
        .then(({ ok, j }) => {
            if (ok) { modal.hide(); dt.ajax.reload(null, false); }
            else showErrors(j.errors || { _: [j.message || @json(__('common.error'))] });
        });
    });
})();
</script>
@endpush

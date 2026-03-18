@extends('layouts.admin')
@section('title', __('shifts.title'))
@section('page-title', __('shifts.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('shifts.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('shifts.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('shifts.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('shifts.search') }}">
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
                    <th>{{ __('shifts.col_date') }}</th>
                    <th>{{ __('shifts.col_time') }}</th>
                    <th>{{ __('shifts.col_employee') }}</th>
                    <th>{{ __('shifts.col_store') }}</th>
                    <th>{{ __('shifts.col_status') }}</th>
                    <th class="text-end pe-4" style="width:180px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">{{ __('shifts.add') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMain">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('shifts.label_employee') }}</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('shifts.label_store') }}</label>
                            <select name="store_id" class="form-select">
                                <option value="">{{ __('common.none') }}</option>
                                @foreach ($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label fw-semibold">{{ __('shifts.label_date') }}</label><input type="date" name="shift_date" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">{{ __('shifts.label_start') }}</label><input type="time" name="starts_at" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">{{ __('shifts.label_end') }}</label><input type="time" name="ends_at" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">{{ __('shifts.label_break') }}</label><input type="number" name="break_minutes" class="form-control" value="0" min="0"></div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">{{ __('shifts.label_status') }}</label>
                            <select name="status" class="form-select">
                                <option value="scheduled">{{ __('shifts.status_scheduled') }}</option>
                                <option value="completed">{{ __('shifts.status_completed') }}</option>
                                <option value="cancelled">{{ __('shifts.status_cancelled') }}</option>
                                <option value="no_show">{{ __('shifts.status_no_show') }}</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label fw-semibold">{{ __('shifts.label_notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
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
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; }
            },
            { data: 'shift_date' }, { data: 'time' }, { data: 'employee' }, { data: 'store' }, { data: 'status' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[1, 'desc']],
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
        bulkUrl: @json(route('work-shifts.bulkDestroy')),
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
        document.getElementById('modalTitle').textContent = @json(__('shifts.add'));
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
            document.getElementById('modalTitle').textContent = @json(__('shifts.edit'));
            showErrors({});
            modal.show();
        });
    });

    $('#dt-table tbody').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: @json(__('shifts.confirm_delete')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: @json(__('common.delete'))
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(wsBase + '/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(r => r.json()).then(() => dt.ajax.reload(null, false));
        });
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
            if (ok) { modal.hide(); dt.ajax.reload(null, false); }
            else showErrors(j.errors || { _: [j.message || @json(__('common.error'))] });
        });
    });
})();
</script>
@endpush

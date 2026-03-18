@extends('layouts.admin')
@section('title', __('performance.title'))
@section('page-title', __('performance.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('performance.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('performance.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAdd">
                <i class="fas fa-plus me-1"></i>{{ __('performance.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('performance.search') }}">
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
                    <th>{{ __('performance.col_employee') }}</th>
                    <th>{{ __('performance.col_period') }}</th>
                    <th>{{ __('performance.col_rating') }}</th>
                    <th>{{ __('performance.col_tables') }}</th>
                    <th>{{ __('performance.col_sales') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">{{ __('performance.add') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMain">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('performance.label_employee') }}</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('performance.label_rating') }}</label>
                            <select name="rating" class="form-select">
                                @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @if($i===3) selected @endif>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('performance.label_period_start') }}</label><input type="date" name="period_start" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('performance.label_period_end') }}</label><input type="date" name="period_end" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('performance.label_tables') }}</label><input type="number" name="tables_served" class="form-control" min="0"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('performance.label_sales') }}</label><input type="number" step="0.01" name="sales_total" class="form-control" min="0"></div>
                        <div class="col-12"><label class="form-label fw-semibold">{{ __('performance.label_comment') }}</label><textarea name="comment" class="form-control" rows="3"></textarea></div>
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
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; }
            },
            { data: 'employee' }, { data: 'period' }, { data: 'rating' }, { data: 'tables' }, { data: 'sales' },
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
        bulkUrl: @json(route('waiter-performance.bulkDestroy')),
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
        document.getElementById('modalTitle').textContent = @json(__('performance.add'));
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
            document.getElementById('modalTitle').textContent = @json(__('performance.edit'));
            showErrors({});
            modal.show();
        });
    });

    $('#dt-table tbody').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: @json(__('performance.confirm_delete')),
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

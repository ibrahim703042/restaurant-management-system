@extends('layouts.admin')
@section('title', __('orders.title'))
@section('page-title', __('orders.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('orders.title') }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('orders.title')" />

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('orders.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-danger btn-sm d-none" id="btnBulkDel">
                        <i class="fas fa-trash me-1"></i>{{ __('common.delete_selected') }}
                        <span class="badge bg-white text-danger ms-1 dt-pro-bulk-count">0</span>
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:42px">
                        <input type="checkbox" class="form-check-input" id="dtCheckAll" aria-label="{{ __('common.delete_selected') }}">
                    </th>
                    <th>{{ __('orders.col_order') }}</th>
                    <th>{{ __('orders.col_client') }}</th>
                    <th>{{ __('orders.col_total') }}</th>
                    <th>{{ __('orders.col_status') }}</th>
                    <th>{{ __('orders.col_date') }}</th>
                    <th class="text-end pe-4" style="width:140px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>

    <div class="modal fade" id="orderNotesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dt-pro-modal border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ __('orders.modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="orderNotesForm">
                    @csrf
                    <div class="modal-body pt-2">
                        <label class="form-label fw-semibold">{{ __('orders.notes_label') }}</label>
                        <textarea name="notes" id="orderNotesText" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('orders.cancel') }}</button>
                        <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('orders.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/dt-pro-bulk.js') }}"></script>
<script>
(function () {
    var listUrl = @json(route('orders.list'));
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var base = @json(url('/orders'));
    var notesOrderId = null;
    var notesModal = new bootstrap.Modal(document.getElementById('orderNotesModal'));

    var dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: listUrl,
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'ps-4' },
            { data: 'order_number' },
            { data: 'client' },
            { data: 'total' },
            { data: 'status' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
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
        bulkUrl: @json(route('orders.bulkDestroy')),
        csrfToken: csrf,
        confirmMsg: @json(__('common.confirm_bulk_delete')),
        deleteLabel: @json(__('orders.delete_btn'))
    });

    $('#dt-table tbody').on('click', '.btn-edit-order', function () {
        var id = $(this).data('id');
        notesOrderId = id;
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); }).then(function (d) {
                document.getElementById('orderNotesText').value = d.order.notes || '';
                notesModal.show();
            });
    });

    document.getElementById('orderNotesForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        fetch(base + '/' + notesOrderId + '/update', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (res) {
            if (res.ok) {
                notesModal.hide();
                Swal.fire({ icon: 'success', title: res.j.message || 'OK', timer: 1200, showConfirmButton: false });
                dt.ajax.reload(null, false);
            } else {
                Swal.fire('Error', res.j.message || 'Error', 'error');
            }
        });
    });

    $('#dt-table tbody').on('click', '.btn-del-order', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: @json(__('orders.confirm_delete')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: @json(__('orders.delete_btn'))
        }).then(function (res) {
            if (!res.isConfirmed) return;
            fetch(base + '/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                if (res.ok) {
                    Swal.fire({ icon: 'success', title: res.j.message, timer: 1200, showConfirmButton: false });
                    dt.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.j.message || 'Error', 'error');
                }
            });
        });
    });
})();
</script>
@endpush

@extends('layouts.admin')
@section('title', __('orders.title'))
@section('page-title', __('orders.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('orders.title') }}</li>
@endsection
@section('main-section')
<x-admin.table-card title="{{ __('orders.title') }}" icon="fas fa-receipt">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>{{ __('orders.col_order') }}</th>
            <th>{{ __('dashboard.client') }}</th>
            <th>{{ __('dashboard.amount') }}</th>
            <th>{{ __('orders.col_status') }}</th>
            <th>{{ __('dashboard.when') }}</th>
            <th style="width:140px">Actions</th>
        </tr>
    </thead>
</x-admin.table-card>

<div class="modal fade" id="orderNotesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('orders.modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="orderNotesForm">
                @csrf
                <div class="modal-body">
                    <label class="form-label">{{ __('orders.notes_label') }}</label>
                    <textarea name="notes" id="orderNotesText" class="form-control" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('orders.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('orders.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
    const listUrl = @json(route('orders.list'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const base = @json(url('/orders'));
    let notesOrderId = null;
    const notesModal = new bootstrap.Modal(document.getElementById('orderNotesModal'));
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: listUrl,
        columns: [
            { data: 'id' },
            { data: 'order_number' },
            { data: 'client' },
            { data: 'total' },
            { data: 'status' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });
    $('#dt-table tbody').on('click', '.btn-edit-order', function () {
        const id = $(this).data('id');
        notesOrderId = id;
        fetch(base + '/' + id + '/json', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => {
                document.getElementById('orderNotesText').value = d.order.notes || '';
                notesModal.show();
            });
    });
    document.getElementById('orderNotesForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch(base + '/' + notesOrderId + '/update', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json().then(j => ({ ok: r.ok, j }))).then(({ ok, j }) => {
            if (ok) {
                notesModal.hide();
                Swal.fire({ icon: 'success', title: j.message || 'OK', timer: 1200, showConfirmButton: false });
                dt.ajax.reload(null, false);
            } else Swal.fire('Error', j.message || 'Error', 'error');
        });
    });
    $('#dt-table tbody').on('click', '.btn-del-order', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: @json(__('orders.confirm_delete')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: @json(__('orders.delete_btn'))
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(base + '/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json().then(j => ({ ok: r.ok, j }))).then(({ ok, j }) => {
                if (ok) {
                    Swal.fire({ icon: 'success', title: j.message, timer: 1200, showConfirmButton: false });
                    dt.ajax.reload(null, false);
                } else Swal.fire('Error', j.message || 'Error', 'error');
            });
        });
    });
})();
</script>
@endpush

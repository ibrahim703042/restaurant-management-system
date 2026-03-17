@extends('layouts.admin')
@section('title', 'Payroll')
@section('page-title', 'Payroll periods')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Payroll</li>
@endsection
@section('main-section')
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><i class="fas fa-file-invoice me-2"></i>Periods</h3>
        @can('hr.payroll.manage')
        <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus me-1"></i>New period</button>
        @endcan
    </div>
    <div class="card-body">
        @cannot('hr.payroll.manage')
        <p class="text-muted small">You can view periods. Ask an admin to create new periods.</p>
        @endcannot
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle w-100" id="dt-table">
                <thead class="table-light"><tr><th>#</th><th>Start</th><th>End</th><th>Status</th><th>Notes</th></tr></thead>
            </table>
        </div>
    </div>
</div>
@can('hr.payroll.manage')
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New payroll period</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="form">@csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-2"><label class="form-label">Period start</label><input type="date" name="period_start" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Period end</label><input type="date" name="period_end" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create</button></div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
@push('scripts')
<script>
(function () {
    const listUrl = @json(route('payroll.list'));
    $('#dt-table').DataTable({
        processing: true, serverSide: true, ajax: { url: listUrl },
        columns: [
            { data: 'id' }, { data: 'period_start' }, { data: 'period_end' }, { data: 'status' }, { data: 'notes' }
        ],
        order: [[0, 'desc']], pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
    @can('hr.payroll.manage')
    const storeUrl = @json(route('payroll.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const modal = new bootstrap.Modal(document.getElementById('modal'));
    const form = document.getElementById('form');
    document.getElementById('btnAdd').addEventListener('click', function () {
        form.reset(); document.getElementById('formErrors').classList.add('d-none'); modal.show();
    });
    form.addEventListener('submit', function (ev) {
        ev.preventDefault(); var err = document.getElementById('formErrors'); err.classList.add('d-none');
        var fd = new FormData(form);
        fetch(storeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(async function (r) { var j = await r.json().catch(function () { return {}; });
                if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                modal.hide(); Swal.fire({ icon: 'success', title: j.message || 'Created', timer: 1500, showConfirmButton: false });
                $('#dt-table').DataTable().ajax.reload(null, false);
            });
    });
    @endcan
})();
</script>
@endpush

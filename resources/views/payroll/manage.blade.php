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
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="search" class="form-control" id="filterSearch" placeholder="Search notes…"></div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="btnApply">Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light"><tr><th>#</th><th>Start</th><th>End</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
        <nav id="pagination" class="mt-2"></nav>
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
    const storeUrl = @json(route('payroll.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const canManage = @json(auth()->user()->can('hr.payroll.manage'));
    let page = 1;
    const tbody = document.getElementById('tbody'), pagination = document.getElementById('pagination');
    const modal = canManage ? new bootstrap.Modal(document.getElementById('modal')) : null;
    const form = document.getElementById('form');
    function loadList(p = 1) {
        page = p;
        fetch(listUrl + '?' + new URLSearchParams({ page, search: document.getElementById('filterSearch').value }), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                tbody.innerHTML = '';
                d.data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.id}</td><td>${row.period_start||''}</td><td>${row.period_end||''}</td><td>${escapeHtml(row.status||'')}</td><td>${escapeHtml((row.notes||'').substring(0,80))}</td>`;
                    tbody.appendChild(tr);
                });
                let h = '<ul class="pagination pagination-sm mb-0">';
                for (let i = 1; i <= d.last_page; i++) h += `<li class="page-item ${i===d.current_page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                pagination.innerHTML = d.last_page > 1 ? h : '';
                pagination.querySelectorAll('[data-page]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); loadList(+a.dataset.page); }));
            });
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
    document.getElementById('btnApply').addEventListener('click', () => loadList(1));
    if (canManage && document.getElementById('btnAdd')) {
        document.getElementById('btnAdd').addEventListener('click', () => { form.reset(); document.getElementById('formErrors').classList.add('d-none'); modal.show(); });
        form.addEventListener('submit', ev => {
            ev.preventDefault(); const err = document.getElementById('formErrors'); err.classList.add('d-none');
            fetch(storeUrl, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
                .then(async r => { const j = await r.json().catch(() => ({}));
                    if (!r.ok) { err.innerHTML = j.errors ? Object.values(j.errors).flat().join('<br>') : j.message; err.classList.remove('d-none'); return; }
                    modal.hide(); Swal.fire({ icon: 'success', title: j.message||'Created', timer: 1500, showConfirmButton: false }); loadList(1);
                });
        });
    }
    loadList(1);
})();
</script>
@endpush

@extends('layouts.admin')
@section('title', __('employees.title'))
@section('page-title', __('employees.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('common.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('employees.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('employees.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-primary btn-sm" id="btnAddEmployee">
                <i class="fas fa-plus me-1"></i>{{ __('employees.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-actions>

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <div class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" class="form-control form-control-sm" id="dtSearchInput" placeholder="{{ __('employees.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm dt-pro-filter-select admin-ts-select" id="filterPosition" data-placeholder="{{ __('employees.filter_all') }}">
                        <option value="">{{ __('employees.filter_all') }}</option>
                        @foreach ($positions as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-dt-pro-outline btn-sm px-3" id="btnApplyFilter">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('common.apply_filter') }}
                    </button>
                    <button class="btn btn-danger btn-sm d-none" id="btnBulkDel">
                        <i class="fas fa-trash me-1"></i>{{ __('common.delete_selected') }} <span class="dt-pro-bulk-count badge bg-white text-danger ms-1"></span>
                    </button>
                </div>
            </div>
        </x-slot:toolbar>
        <table id="dt-table" class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:42px"><input type="checkbox" class="form-check-input" id="dtCheckAll"></th>
                    <th>{{ __('employees.col_name') }}</th>
                    <th>{{ __('employees.col_email') }}</th>
                    <th>{{ __('employees.col_phone') }}</th>
                    <th>{{ __('employees.col_position') }}</th>
                    <th class="text-end pe-4" style="width:160px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
        </table>
    </x-admin.data-table-pro>
</div>

<div class="modal fade" id="employeeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content dt-pro-modal">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="employeeModalTitle">{{ __('employees.add') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="employeeForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="employeeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6"><x-admin.input name="fname" :label="__('employees.label_first_name')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="lname" :label="__('employees.label_last_name')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="email" type="email" :label="__('employees.label_email')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="phone" :label="__('employees.label_phone')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.radio-group name="gender" :label="__('employees.label_gender')" :options="[__('employees.gender_male') => __('employees.gender_male'), __('employees.gender_female') => __('employees.gender_female'), __('employees.gender_other') => __('employees.gender_other')]" :selected="__('employees.gender_male')" />
                        </div>
                        <div class="col-md-6"><x-admin.input name="birthdate" type="date" :label="__('employees.label_birthday')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6">
                            <x-admin.select-search name="position_id" id="selEmpPosition" :label="__('employees.label_position')" required wrapperClass="mb-0"
                                createUrl="{{ route('positions.index') }}" :createLabel="__('common.new_position')">
                                @foreach ($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </x-admin.select-search>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-semibold">{{ __('employees.label_photo') }}</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="col-md-6"><x-admin.input name="mother" :label="__('employees.label_mother')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="father" :label="__('employees.label_father')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="country" :label="__('employees.label_country')" wrapperClass="mb-0" required /></div>
                        <div class="col-md-6"><x-admin.input name="city" :label="__('employees.label_city')" wrapperClass="mb-0" required /></div>
                        <div class="col-12"><x-admin.input name="address" :label="__('employees.label_address')" wrapperClass="mb-0" required /></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4" id="employeeFormSubmit">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
    const listUrl = @json(route('employees.list'));
    const storeUrl = @json(route('employees.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let editingId = null;
    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    const form = document.getElementById('employeeForm');
    const selPos = document.getElementById('selEmpPosition');
    function ensureTs(sel) {
        if (sel && sel.classList.contains('admin-ts-select') && window.adminTomSelectInitOne && !sel.tomselect) {
            window.adminTomSelectInitOne(sel);
        }
    }
    const dt = $('#dt-table').DataTable({
        serverSide: true,
        ajax: { url: listUrl, data: function (d) {
            var f = document.getElementById('filterPosition');
            d.position_id = f.tomselect ? f.tomselect.getValue() : f.value;
        } },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'ps-4',
              render: function(data, type, row) { return '<input type="checkbox" class="form-check-input dt-pro-row-check" value="' + row.id + '">'; } },
            { data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'position' },
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

    dtProBulk({ dt: dt, bulkUrl: @json(route('employees.bulkDestroy')), csrfToken: csrf, confirmMsg: @json(__('common.confirm_bulk_delete')), deleteLabel: @json(__('common.delete')) });

    document.getElementById('btnApplyFilter').addEventListener('click', function () { dt.ajax.reload(); });
    $('#dt-table tbody').on('click', '.btn-edit', function () { openEdit($(this).data('id')); });
    $('#dt-table tbody').on('click', '.btn-del', function () { confirmDelete($(this).data('id')); });

    document.getElementById('btnAddEmployee').addEventListener('click', () => {
        editingId = null;
        document.getElementById('employeeModalTitle').textContent = @json(__('employees.add'));
        form.reset();
        form.querySelector('[name="gender"][value="Male"]').checked = true;
        ensureTs(selPos);
        if (selPos.tomselect && selPos.options[0]) selPos.tomselect.setValue(selPos.options[0].value, true);
        document.getElementById('employeeFormErrors').classList.add('d-none');
        modal.show();
    });

    function openEdit(id) {
        editingId = id;
        document.getElementById('employeeModalTitle').textContent = @json(__('employees.edit'));
        document.getElementById('employeeFormErrors').classList.add('d-none');
        fetch(`{{ url('/employee') }}/${id}/json`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(({ employee: e }) => {
                form.fname.value = e.first_name;
                form.lname.value = e.last_name;
                form.email.value = e.email;
                form.phone.value = e.phone;
                form.querySelectorAll('[name="gender"]').forEach(function (r) { r.checked = r.value === e.gender; });
                form.birthdate.value = (e.birthday || '').substring(0, 10);
                ensureTs(selPos);
                if (selPos.tomselect) selPos.tomselect.setValue(String(e.position_id), true);
                else selPos.value = e.position_id;
                form.mother.value = e.mother_name;
                form.father.value = e.father_name;
                form.country.value = e.country;
                form.city.value = e.city;
                form.address.value = e.address;
                form.image.value = '';
                modal.show();
            });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        const errBox = document.getElementById('employeeFormErrors');
        errBox.classList.add('d-none');
        const fd = new FormData(form);
        const url = editingId ? `{{ url('/employee') }}/${editingId}/update` : storeUrl;
        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
            .then(async r => {
                const j = await r.json().catch(() => ({}));
                if (!r.ok) {
                    let msg = j.message || @json(__('common.error'));
                    if (j.errors) {
                        msg = Object.values(j.errors).flat().join('<br>');
                    }
                    errBox.innerHTML = msg;
                    errBox.classList.remove('d-none');
                    return;
                }
                modal.hide();
                Swal.fire({ icon: 'success', title: j.message || @json(__('common.saved')), timer: 1500, showConfirmButton: false });
                dt.ajax.reload(null, false);
            });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: @json(__('employees.confirm_delete')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: @json(__('common.delete')),
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/employee') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            })
                .then(async r => {
                    const j = await r.json().catch(() => ({}));
                    if (!r.ok) { Swal.fire(@json(__('common.error')), j.message || @json(__('common.error')), 'error'); return; }
                    Swal.fire({ icon: 'success', title: @json(__('common.removed')), timer: 1200, showConfirmButton: false }); dt.ajax.reload(null, false);
                });
        });
    }
})();
</script>
@endpush

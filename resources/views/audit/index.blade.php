@extends('layouts.admin')
@section('title', __('audit.title'))
@section('page-title', __('audit.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('audit.title') }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('audit.title')" />

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <form method="get" class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('audit.search') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select name="user_id" class="form-select form-select-sm dt-pro-filter-select">
                        <option value="">{{ __('audit.filter_all_users') }}</option>
                        @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" style="width:auto">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" style="width:auto">
                    <button type="submit" class="btn btn-dt-pro-outline btn-sm px-3">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('audit.filter_btn') }}
                    </button>
                </div>
            </form>
        </x-slot:toolbar>

        <table class="table align-middle w-100 mb-0">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('audit.col_when') }}</th>
                    <th>{{ __('audit.col_user') }}</th>
                    <th>{{ __('audit.col_method') }}</th>
                    <th>{{ __('audit.col_route') }}</th>
                    <th>{{ __('audit.col_path') }}</th>
                    <th>{{ __('audit.col_http') }}</th>
                    <th class="pe-4">{{ __('audit.col_details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td class="ps-4 text-nowrap small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="small">{{ $log->user?->name ?? '—' }}</td>
                    <td><span class="badge bg-secondary">{{ $log->method }}</span></td>
                    <td class="small">{{ $log->route_name ?? '—' }}</td>
                    <td class="small text-break">{{ $log->path }}</td>
                    <td>{{ $log->response_status }}</td>
                    <td class="small pe-4">
                        @if($log->payload_summary)
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#p{{ $log->id }}">{{ __('audit.btn_payload') }}</button>
                        <div class="collapse mt-1" id="p{{ $log->id }}"><pre class="mb-0 p-2 bg-light rounded small text-start" style="max-height:200px;overflow:auto">{{ json_encode($log->payload_summary, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div>
                        @else — @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">{{ __('audit.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($logs->hasPages())
        <x-slot:footer>
            <div class="dt-pro-pagination-wrapper ms-auto">
                {{ $logs->withQueryString()->links() }}
            </div>
        </x-slot:footer>
        @endif
    </x-admin.data-table-pro>
</div>
@endsection

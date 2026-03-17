@extends('layouts.admin')
@section('title', 'Audit log')
@section('page-title', 'Audit log')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
<li class="breadcrumb-item active">Audit</li>
@endsection
@section('main-section')
<div class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>User actions</h3></div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">All users</option>
                    @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-md-3"><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Action / path…"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="table-light">
                    <tr><th>When</th><th>User</th><th>Method</th><th>Route</th><th>Path</th><th>HTTP</th><th>Details</th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="small">{{ $log->user?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $log->method }}</span></td>
                        <td class="small">{{ $log->route_name ?? '—' }}</td>
                        <td class="small text-break">{{ $log->path }}</td>
                        <td>{{ $log->response_status }}</td>
                        <td class="small">
                            @if($log->payload_summary)
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#p{{ $log->id }}">Payload</button>
                            <div class="collapse mt-1" id="p{{ $log->id }}"><pre class="mb-0 p-2 bg-light rounded small text-start" style="max-height:200px;overflow:auto">{{ json_encode($log->payload_summary, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div>
                            @else — @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">No entries yet. Actions appear after POST/PUT/PATCH/DELETE requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection

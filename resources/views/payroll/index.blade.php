@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Payroll periods</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @can('hr.payroll.manage')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary mb-3">New period</a>
    @endcan
    <div class="card"><div class="card-body table-responsive">
        <table class="table"><thead><tr><th>Start</th><th>End</th><th>Status</th></tr></thead>
        <tbody>
            @foreach ($periods as $p)
            <tr><td>{{ $p->period_start }}</td><td>{{ $p->period_end }}</td><td>{{ $p->status }}</td></tr>
            @endforeach
        </tbody></table>
        {{ $periods->links() }}
    </div></div>
</div>
@endsection

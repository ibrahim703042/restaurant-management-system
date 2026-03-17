@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Clients</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <a href="{{ route('clients.create') }}" class="btn btn-primary mb-3">Add client</a>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Name</th><th>Phone</th><th>Debt balance</th><th></th></tr></thead>
                <tbody>
                    @foreach ($clients as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ number_format($c->debt_balance, 0) }}</td>
                        <td><a href="{{ route('clients.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection

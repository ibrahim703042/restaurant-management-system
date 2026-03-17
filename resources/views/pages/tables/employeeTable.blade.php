@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Employees</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <a href="{{ route('employee.create') }}" class="btn btn-primary mb-3">Add employee</a>
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Photo</th><th>Name</th><th>Email</th><th>Phone</th><th></th></tr></thead>
            <tbody>
                @foreach ($employees as $e)
                <tr>
                    <td>
                        @if($e->image && $e->image !== 'default.png')
                            <img src="{{ asset('storage/'.$e->image) }}" width="40" height="40" class="rounded-circle object-fit-cover" alt="">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $e->first_name }} {{ $e->last_name }}</td>
                    <td>{{ $e->email }}</td>
                    <td>{{ $e->phone }}</td>
                    <td>
                        <a href="{{ url('/edit-employee/'.$e->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="{{ url('/delete-employee/'.$e->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
</div>
@endsection

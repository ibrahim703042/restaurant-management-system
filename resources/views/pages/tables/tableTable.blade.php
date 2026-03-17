
@extends('layouts.admin')

@section('main-section')

    <div class="container-fluid px-4">
        <h1 class="mt-4">Tables</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item">
                <a href="{{ url('/') }}">
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active">
                Table details
            </li>
        </ol>
        @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
        <div class="card mb-4">
            <div class="card-header bg-danger d-flex justify-content-between align-items-center">
                <h4 class="text-light">
                    <i class="fas fa-table me-1"></i>
                    Manage table
                </h3>


                    <a href="{{ route('table.create') }}" class="btn btn-primary shadow">
                        <i class=" fa fa-plus-circle me-2"></i>
                        Add new table
                    </a>

            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Table</th>
                            <th>Section</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Store</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->table_name }}</td>
                                <td>{{ $item->section ?? '—' }}</td>
                                <td>{{ $item->capacity }}</td>
                                @if ($item->status == 1)
                                    <td>Available</td>
                                @elseif ($item->status == 2)
                                    <td>Taken</td>
                                @else
                                    <td>Reserved</td>
                                @endif
                                <td>{{ $item->store_name ?? '—' }}</td>
                                <td>
                                    <a href="{{ url('/edit-table/'.$item->id) }}" class="text-decoration-none text-success mx-1">
                                        update
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('/delete-table/'.$item->id) }}" class="text-decoration-none text-danger mx-1">
                                    delete
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>
@endsection

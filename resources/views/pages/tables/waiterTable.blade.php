
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
                Employee details
            </li>
        </ol>
        @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
        <div class="card mb-4">
            <div class="card-header bg-danger d-flex justify-content-between align-items-center">
                <h4 class="text-light">
                    <i class="fas fa-users me-1"></i>
                    Manage Employee
                </h3>


                    <a href="{{ route('employees.index') }}" class="btn btn-primary shadow">
                        <i class=" fa fa-plus-circle me-2"></i>
                        Employees (add / edit)
                    </a>

            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th> # </th>
                            <th> Profil </th>
                            <th> Full name</th>
                            <th> E-mail</th>
                            <th> Gender </th>
                            <th> Birthday</th>
                            <th> Phone</th>
                            <th colspan="3"> Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <img src="storage/images/' .$item->picture. '" width="50" class="img-thumbnail rounded-circle">
                                </td>
                                <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->gender }}</td>
                                <td>{{ $item->birthday }}</td>
                                <td>{{ $item->phone}}</td>
                                <td>
                                    <a href="#" id="" class="text-decoration-none text-primary mx-1 deleteIcon">
                                    View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('employees.index') }}" class="text-decoration-none text-success mx-1">
                                        Manage
                                    </a>
                                </td>
                                <td>
                                    <span class="text-muted small">Use Employees page</span>
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

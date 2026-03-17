
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
                Products details
            </li>
        </ol>
        @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
        <div class="card mb-4">
            <div class="card-header bg-danger d-flex justify-content-between align-items-center">
                <h4 class="text-light">
                    <i class="fas fa-users me-1"></i>
                    Manage product
                </h3>


                    <a href="{{ route('product.create') }}" class="btn btn-primary shadow">
                        <i class=" fa fa-plus-circle me-2"></i>
                        Add new product
                    </a>

            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th> # </th>
                            <th> Image</th>
                            <th> Product name</th>
                            <th> Price</th>
                            <th> Category </th>
                            <th> Store</th>
                            <th> Status</th>
                            <th> Description</th>
                            <th colspan="3"> Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    @if($item->image && !str_starts_with($item->image, 'http'))
                                        <img src="{{ asset('storage/'.$item->image) }}" width="50" height="50" class="img-thumbnail rounded-circle object-fit-cover" alt="">
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->price }}</td>
                                <td>{{ $item->category_id }}</td>
                                <td>{{ $item->store_id }}</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ $item->description}}</td>
                                <td>
                                    <a href="#" id="" class="text-decoration-none text-primary mx-1 deleteIcon">
                                    View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('/edit-product/'.$item->id) }}" class="text-decoration-none text-success mx-1">
                                        update
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('/delete-product/'.$item->id) }}" class="text-decoration-none text-danger mx-1">
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

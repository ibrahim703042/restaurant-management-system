@extends('layouts.admin')

@section('main-section')

<div class="container-fluid px-4">
    <h1 class="mt-4">Register form</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active">
            Add Table
        </li>
    </ol>

    <body class="bg-light">

        <div class="row">
            @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
            <div class="card mb-1 bg-light">
                <div class="card-body">

                    <form method="POST"  action="{{ url('/add-table')}}">
                        @csrf
                            <div class="row g-3">

                                <div class="col-md-6 mt-2 border-right">
                                    <div class="row g-2">



                                    </div>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="row g-2">

                                        <div class="col-md-12">
                                            <label for="name" class="col-md-12 col-form-label text-md-start">{{ __('Name') }}<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            name="name" id="name" placeholder=" Enter product name" value="{{ old('name') }}" autocomplete="name" required>

                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Section (e.g. Patio)</label>
                                            <input type="text" name="section" class="form-control" value="{{ old('section') }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Sort order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="capacity" class="col-md-12 col-form-label text-md-start">{{ __('Capacity') }}<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('capacity') is-invalid @enderror"
                                            name="capacity" id="capacity" placeholder=" Enter number of chair" value="{{ old('capacity') }}" autocomplete="capacity" required>

                                            @error('capacity')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="status" class="col-form-label text-md-start">{{ __('Status') }}<span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror"
                                            name="status" id="status" value="{{ old('status') }}"  required>
                                                <option value="" selected disabled>Choose status</option>
                                                <option value="1">Available</option>
                                                <option value="2">Taken</option>
                                                <option value="3">Reserve</option>
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="store" class="form-label">{{ __('Store') }}<span class="text-danger">*</span></label>
                                            <select required class="form-select @error('') is-invalid @enderror"
                                            name="store" id="store" value="{{ old('store') }}">

                                                <option selected disabled>Choose store</option>

                                                @foreach (DB::table('stores')->get() as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach

                                            </select>
                                            @error('store')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        {{--  <div class="col-md-12">
                                            <label for="date" class="col-md-12 col-form-label text-md-start">{{ __('Date') }}<span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('birthdate') is-invalid @enderror"
                                            name="date" id="date" value="{{ old('date') }}" required>
                                            @error('date')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>  --}}

                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-end p-3 py-2 ">
                                    <button type="submit"  class="btn btn-dark">
                                        {{ __('Add table') }}
                                    </button>
                                </div>

                            </div>

                    </form>
                </div>
            </div>
        </div>

    </body>

</div>

@endsection

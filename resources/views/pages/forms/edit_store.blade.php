@extends('layouts.admin')

@section('main-section')

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit form</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active">
            Edit Store
        </li>
    </ol>

    <body class="bg-light">

        <div class="row">
            @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
            <div class="card mb-1 bg-light">
                <div class="card-body">

                    <form method="POST" action="{{ url('/update-store/'.$store->id) }}">

                        @method('PUT')
                        @csrf

                            <div class="row g-3">

                                <div class="col-md-6 mt-2 border-right">

                                </div>

                                <div class="col-md-6 mt-2 border-right">
                                    <div class="row g-2">

                                        <div class="col-md-12">
                                            <label for="name" class="col-md-12 col-form-label text-md-start">{{ __('Name') }}<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            name="name" id="name" value="{{$store->name}}">

                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Code</label>
                                            <input type="text" name="code" class="form-control" value="{{ old('code', $store->code) }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control" value="{{ old('address', $store->address) }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $store->phone) }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $store->notes) }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_primary_stock_location" value="1" id="ps" {{ old('is_primary_stock_location', $store->is_primary_stock_location) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ps">Primary stock location</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="status" class="col-form-label text-md-start">{{ __('Status') }}<span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror"
                                            name="status" id="status" value="{{ old('status') }}">
                                                {{--  <option value="{{$store->status}}">{{$store->status}}</option>  --}}
                                                <option value="{{$store->status}}">
                                                    @if ( $store->status === 1)
                                                        Active

                                                    @elseif ($store->status === 2 )
                                                        Disactive
                                                    @else
                                                        On way
                                                    @endif
                                                </option>
                                                <option value="" disabled>Choose status</option>
                                                <option value="1">Active</option>
                                                <option value="2">Disactive</option>
                                                <option value="3">On way</option>
                                            </select>
                                            @error('status')
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
                                        {{ __('Update store') }}
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

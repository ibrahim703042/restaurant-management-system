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
            Add Employee
        </li>
    </ol>

    <body class="bg-light">

        <div class="row">
            @if (session('status'))
            <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
            <div class="card mb-1 bg-light">
                <div class="card-body">

                    <form method="POST" id="add_student" enctype="multipart/form-data" action="{{ url('/add-employee')}}">
                        @csrf
                            <div class="row g-3">
                                <div class="col-md-2 border-right">
                                    <div class="d-flex flex-column align-items-center text-center p-3 py-2">
                                        <img class="rounded-circle mt-2" width="100px"
                                            src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg">

                                        {{--  <!--upload image-->  --}}
                                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                                        name="image" id="image">

                                    </div>
                                </div>

                                <div class="col-md-5 mt-2 border-right">

                                    <div class="row g-2">

                                        <div class="col-md-6">
                                            <label for="fname" class="col-md-12 col-form-label text-md-start">{{ __('First name') }}<span class="text-danger">*</span></label>
                                            <input id="fname" required type="text" class="form-control @error('fname') is-invalid @enderror" name="fname"  placeholder=" Enter first name" value="{{ old('fname') }}" autocomplete="fname" autofocus>

                                            @error('fname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lname" class="col-md-12 col-form-label text-md-start">{{ __('Last name') }}<span class="text-danger">*</span></label>
                                            <input id="lname" required type="text" class="form-control @error('lname') is-invalid @enderror" name="lname" placeholder=" Enter last name" value="{{ old('lname') }}" autocomplete="lname" autofocus>

                                            @error('lname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="email" class="col-md-12 col-form-label text-md-start">{{ __('Email Address') }}<span class="text-danger">*</span></label>
                                            <input id="email" required type="email" class="form-control @error('email') is-invalid @enderror" name="email"  placeholder=" @biu.bi" value="{{ old('email') }}" autocomplete="email">

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="gender" class="col-form-label text-md-start">{{ __('Gender') }}<span class="text-danger">*</span></label>
                                            <select id="gender" required class="form-select @error('gender') is-invalid @enderror" name="gender" id="gender" value="{{ old('gender') }}">
                                                <option value="" selected disabled>Choose your gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            @error('gender')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="bithdate" class="col-md-12 col-form-label text-md-start">{{ __('Birth day') }}<span class="text-danger">*</span></label>
                                            <input required type="date" class="form-control @error('birthdate') is-invalid @enderror" name="birthdate" id="birthdate" value="{{ old('birthdate') }}">
                                            @error('birthdate')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="phone" class="col-md-12 col-form-label text-md-start">{{ __('Phone') }}<span class="text-danger">*</span></label>
                                            <input required id="phone" type="phone" class="form-control @error('phone') is-invalid @enderror"  placeholder=" Enter phone" name="phone" value="{{ old('phone') }}" autocomplete="phone" autofocus>

                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-5 mt-2 border-right">

                                    <div class="row g-2">

                                        <div class="col-md-12">
                                            <label for="name" class="col-md-12 col-form-label text-md-start">{{ __('Mother Name') }}<span class="text-danger">*</span></label>
                                            <input id="mother" required type="text" class="form-control @error('mother') is-invalid @enderror"  placeholder=" Enter mother name" name="mother" value="{{ old('mother') }}" autocomplete="mother" autofocus>

                                            @error('mother')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="father" class="col-md-12 col-form-label text-md-start">{{ __('Father') }}<span class="text-danger">*</span></label>
                                            <input id="father" required type="text" class="form-control @error('father') is-invalid @enderror" placeholder=" Enter father name" name="father" value="{{ old('father') }}" autocomplete="father" autofocus>

                                            @error('father')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="country" class="col-md-12 col-form-label text-md-start">{{ __('Country') }}<span class="text-danger">*</span></label>
                                            <input id="country" required type="text" class="form-control @error('country') is-invalid @enderror"  placeholder=" Enter Country" name="country" value="{{ old('country') }}" autocomplete="country" autofocus>

                                            @error('country')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="city" class="col-md-12 col-form-label text-md-start">{{ __('City') }}<span class="text-danger">*</span></label>
                                            <input id="city" required type="text" class="form-control @error('city') is-invalid @enderror" placeholder=" Enter City" name="city" value="{{ old('city') }}" autocomplete="city" autofocus>

                                            @error('city')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="address" class="col-md-12 col-form-label text-md-start">{{ __('Address') }}<span class="text-danger">*</span></label>
                                            <input id="address" required type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Enter address" name="address" value="{{ old('address') }}" autocomplete="address" autofocus>

                                            @error('address')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Position</label>
                                    <select name="position_id" class="form-select" required>
                                        @foreach(\App\Models\Position::orderBy('title')->get() as $pos)
                                        <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex flex-column align-items-end p-3 py-2 ">
                                    <button type="submit"  class="btn btn-dark">
                                        {{ __('Add employee') }}
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

@extends('layouts.auth')

@section('title', __('Register'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card auth-card mx-auto">
            <div class="row g-0 align-items-stretch">
                <div class="col-md-5 col-lg-4 d-none d-lg-block auth-visual-wrap">
                    <img src="{{ route('auth.media', ['file' => 'login.png']) }}" alt="" loading="lazy">
                </div>
                <div class="col-12 col-lg-8">
                    <div class="auth-form-panel" style="max-height: 90vh; overflow-y: auto;">
                        @include('auth.partials.brand')
                        <p class="auth-subtitle mb-2">{{ __('New account') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-4">{{ __('Register') }}</h1>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-medium">{{ __('Name') }}</label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-medium">{{ __('Email Address') }}</label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password-confirm" class="form-label fw-medium">{{ __('Confirm Password') }}</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="submit" class="btn btn-auth-primary text-white px-4">
                                    <i class="fas fa-user-plus me-2"></i>{{ __('Register') }}
                                </button>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">{{ __('Back to login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

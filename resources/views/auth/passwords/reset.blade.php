@extends('layouts.auth')

@section('title', __('Reset Password'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card auth-card mx-auto">
            <div class="row g-0 align-items-stretch">
                <div class="col-md-5 d-none d-md-block auth-visual-wrap">
                    <img src="{{ route('auth.media', ['file' => 'login.png']) }}" alt="" loading="lazy">
                </div>
                <div class="col-md-7">
                    <div class="auth-form-panel">
                        @include('auth.partials.brand')
                        <p class="auth-subtitle mb-2">{{ __('New password') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-4">{{ __('Reset Password') }}</h1>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-medium">{{ __('Confirm Password') }}</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn btn-auth-primary text-white w-100">
                                <i class="fas fa-key me-2"></i>{{ __('Reset Password') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
                        <p class="auth-subtitle mb-2">{{ __('Account recovery') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-3">{{ __('Reset Password') }}</h1>
                        <p class="text-muted small mb-4">{{ __('We will email you a 6-digit code. Then you can set a new password.') }}</p>

                        @if (session('status'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label fw-medium">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="you@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-auth-primary text-white w-100 mb-3">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('Send verification code') }}
                            </button>
                            <div class="auth-links text-center">
                                <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

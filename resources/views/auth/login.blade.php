@extends('layouts.auth')

@section('title', __('Login'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card auth-card mx-auto">
            <div class="row g-0 align-items-stretch min-vh-50" style="min-height: min(32rem, 70vh);">
                <div class="col-md-5 col-lg-5 d-none d-md-block auth-visual-wrap">
                    <img src="{{ route('auth.media', ['file' => 'login.png']) }}" alt="" width="600" height="800" loading="eager">
                </div>
                <div class="col-12 col-md-7 col-lg-7 d-flex">
                    <div class="auth-form-panel flex-grow-1 d-flex flex-column justify-content-center">
                        <div class="d-md-none rounded-3 overflow-hidden mb-3 shadow-sm" style="max-height: 11rem;">
                            <img src="{{ route('auth.media', ['file' => 'login.png']) }}" alt="" class="w-100 h-100 object-fit-cover" style="object-fit: cover; min-height: 8rem;">
                        </div>
                        @include('auth.partials.brand')
                        <p class="auth-subtitle mb-2">{{ __('Welcome back') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-4">{{ __('Sign into your account') }}</h1>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="you@example.com">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                            </div>
                            <button type="submit" class="btn btn-auth-primary btn-lg text-white w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login') }}
                            </button>
                            <div class="auth-links small text-center text-md-start">
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                                @endif
                                @if (Route::has('register'))
                                    <span class="text-muted mx-1">·</span>
                                    <a href="{{ route('register') }}">{{ __('Create an account') }}</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

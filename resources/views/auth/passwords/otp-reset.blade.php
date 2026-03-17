@extends('layouts.auth')

@section('title', __('New password'))

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
                        <h1 class="h4 fw-bold text-dark mb-2">{{ __('Enter code & new password') }}</h1>
                        <p class="text-muted small mb-3">
                            {{ __('We sent a 6-digit code to') }} <strong>{{ $email }}</strong>.
                            <a href="{{ route('password.otp.cancel') }}" class="text-decoration-none">{{ __('Change email') }}</a>
                        </p>

                        @if (session('status'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.otp.update') }}" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label for="otp" class="form-label fw-medium">{{ __('Verification code') }}</label>
                                <input id="otp" type="text" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                                    class="form-control form-control-lg text-center @error('otp') is-invalid @enderror" style="letter-spacing:0.35em;font-weight:600;"
                                    value="{{ old('otp') }}" required autofocus placeholder="000000">
                                @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">{{ __('New password') }}</label>
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" minlength="8">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-medium">{{ __('Confirm new password') }}</label>
                                <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password" minlength="8">
                            </div>
                            <button type="submit" class="btn btn-auth-primary text-white w-100 mb-3">
                                <i class="fas fa-key me-2"></i>{{ __('Update password') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('password.otp.resend') }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                                <i class="fas fa-redo me-1"></i>{{ __('Resend code') }}
                            </button>
                        </form>

                        <div class="auth-links text-center">
                            <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to login') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

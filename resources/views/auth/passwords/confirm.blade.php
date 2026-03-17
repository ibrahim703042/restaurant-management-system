@extends('layouts.auth')

@section('title', __('Confirm Password'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card auth-card mx-auto">
            <div class="row g-0 align-items-stretch">
                <div class="col-md-5 d-none d-md-block auth-visual-wrap">
                    <img src="{{ route('auth.media', ['file' => 'login.png']) }}" alt="" loading="lazy">
                </div>
                <div class="col-md-7">
                    <div class="auth-form-panel">
                        @include('auth.partials.brand')
                        <p class="auth-subtitle mb-2">{{ __('Security check') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-3">{{ __('Confirm Password') }}</h1>
                        <p class="text-muted small mb-4">{{ __('Please confirm your password before continuing.') }}</p>

                        <form method="POST" action="{{ route('password.confirm') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="password" class="form-label fw-medium">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-auth-primary text-white w-100 mb-3">
                                {{ __('Confirm Password') }}
                            </button>
                            @if (Route::has('password.request'))
                                <div class="text-center auth-links">
                                    <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

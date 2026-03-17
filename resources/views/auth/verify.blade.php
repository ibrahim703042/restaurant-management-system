@extends('layouts.auth')

@section('title', __('Verify Email'))

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
                        <p class="auth-subtitle mb-2">{{ __('Almost there') }}</p>
                        <h1 class="h4 fw-bold text-dark mb-3">{{ __('Verify Your Email Address') }}</h1>

                        @if (session('resent'))
                            <div class="alert alert-success border-0 shadow-sm mb-4">
                                <i class="fas fa-envelope-open-text me-2"></i>{{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endif

                        <p class="text-muted mb-3">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                        <div class="text-muted small mb-4">
                            {{ __('If you did not receive the email') }},
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 align-baseline auth-links" style="font-size: inherit;">{{ __('click here to request another') }}</button>
                            </form>.
                        </div>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i>{{ __('Home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

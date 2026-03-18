@extends('layouts.admin')
@section('title', __('profile.title'))
@section('page-title', __('profile.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('profile.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    <x-admin.page-actions :title="__('profile.title')" />

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="row g-0">
            {{-- Left column: avatar + identity --}}
            <div class="col-lg-3 dt-pro-profile-left text-center py-5 px-4">
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="dt-pro-profile-avatar mb-3">
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-3">{{ $user->email }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-1">
                    @forelse ($user->getRoleNames() as $role)
                    <span class="badge rounded-pill" style="background:var(--dt-pro-accent);color:#fff">{{ $role }}</span>
                    @empty
                    <span class="text-muted small">{{ __('profile.no_roles') }}</span>
                    @endforelse
                </div>
            </div>

            {{-- Right column: tabbed info --}}
            <div class="col-lg-9">
                <ul class="nav dt-pro-tabs border-bottom px-4 pt-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGeneral" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>{{ __('profile.tab_general') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPreferences" type="button" role="tab">
                            <i class="fas fa-cog me-1"></i>{{ __('profile.tab_preferences') }}
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4">
                    {{-- General tab --}}
                    <div class="tab-pane fade show active" id="tabGeneral" role="tabpanel">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ __('profile.employee') }}</h6>
                        @if ($user->employee)
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.name') }}</div>
                                <div class="fw-medium">{{ $user->employee->first_name }} {{ $user->employee->last_name }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.position') }}</div>
                                <div class="fw-medium">{{ $user->employee->position->title ?? '—' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.phone') }}</div>
                                <div class="fw-medium">{{ $user->employee->phone ?? '—' }}</div>
                            </div>
                            @if ($user->employee->hire_date)
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.hire_date') }}</div>
                                <div class="fw-medium">{{ \Carbon\Carbon::parse($user->employee->hire_date)->format('d M Y') }}</div>
                            </div>
                            @endif
                        </div>
                        @else
                        <p class="text-muted mb-0">{{ __('profile.no_employee') }}</p>
                        @endif

                        <hr class="my-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ __('profile.account_info') }}</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.email') }}</div>
                                <div class="fw-medium">{{ $user->email }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('profile.member_since') }}</div>
                                <div class="fw-medium">{{ $user->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Preferences tab --}}
                    <div class="tab-pane fade" id="tabPreferences" role="tabpanel">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ __('profile.language') }}</h6>
                        <div class="d-flex gap-2 mb-4">
                            @foreach (config('app.available_locales', ['en' => 'English', 'fr' => 'Français']) as $code => $label)
                            <a href="{{ route('locale.set', $code) }}"
                               class="btn btn-sm {{ app()->getLocale() === $code ? 'btn-dt-pro-primary' : 'btn-dt-pro-outline' }} rounded-pill px-3">
                                {{ $label }}
                            </a>
                            @endforeach
                        </div>

                        <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ __('profile.timezone') }}</h6>
                        <p class="text-muted small">{{ __('profile.timezone_placeholder') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

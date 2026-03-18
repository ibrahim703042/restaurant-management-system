@extends('layouts.admin')
@section('title', __('profile.title'))
@section('page-title', __('profile.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('profile.title') }}</li>
@endsection
@section('main-section')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-header border-0 text-white py-4" style="background: linear-gradient(135deg, #3c8dbc 0%, #2c6d8f 100%);">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $user->avatarUrl() }}" alt="" class="rounded-circle border border-3 border-white shadow" width="88" height="88" style="object-fit:cover;">
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <div class="opacity-90">{{ $user->email }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <h6 class="text-uppercase text-muted small mb-3">{{ __('profile.roles') }}</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @forelse ($user->getRoleNames() as $role)
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">{{ $role }}</span>
                    @empty
                    <span class="text-muted">—</span>
                    @endforelse
                </div>
                <h6 class="text-uppercase text-muted small mb-3">{{ __('profile.employee') }}</h6>
                @if ($user->employee)
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('profile.name') }}</dt>
                    <dd class="col-sm-8">{{ $user->employee->first_name }} {{ $user->employee->last_name }}</dd>
                    <dt class="col-sm-4">{{ __('profile.position') }}</dt>
                    <dd class="col-sm-8">{{ $user->employee->position->title ?? '—' }}</dd>
                    <dt class="col-sm-4">{{ __('profile.phone') }}</dt>
                    <dd class="col-sm-8">{{ $user->employee->phone ?? '—' }}</dd>
                </dl>
                @else
                <p class="text-muted mb-0">{{ __('profile.no_employee') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

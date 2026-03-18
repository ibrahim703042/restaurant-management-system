@php
    $u = auth()->user()?->loadMissing('employee');
    $loc = app()->getLocale();
@endphp
<nav class="main-header navbar navbar-expand navbar-white navbar-light bg-white border-bottom shadow-sm">
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <button class="nav-link btn btn-link text-dark px-2" type="button" id="sidebarToggleBtn" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </li>
        <li class="nav-item d-none d-md-inline-block">
            <a href="{{ route('admin.index') }}" class="nav-link fw-medium">{{ __('nav.home') }}</a>
        </li>
    </ul>
    <ul class="navbar-nav ms-auto align-items-center flex-row gap-1 gap-md-2">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center py-1 px-2" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('nav.language') }}">
                <i class="fas fa-globe me-1 text-primary"></i>
                <span class="d-none d-sm-inline small text-uppercase fw-semibold">{{ $loc }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><h6 class="dropdown-header">{{ __('nav.language') }}</h6></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center {{ $loc === 'en' ? 'active' : '' }}" href="{{ route('locale.set', ['locale' => 'en']) }}">
                        <span class="me-2">🇬🇧</span> {{ __('nav.lang_en') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center {{ $loc === 'fr' ? 'active' : '' }}" href="{{ route('locale.set', ['locale' => 'fr']) }}">
                        <span class="me-2">🇫🇷</span> {{ __('nav.lang_fr') }}
                    </a>
                </li>
            </ul>
        </li>
        @auth
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1 ps-2 pe-3 rounded-pill bg-light" href="#" data-bs-toggle="dropdown" data-bs-display="static">
                <img src="{{ $u->avatarUrl() }}" alt="" class="rounded-circle border border-2 border-white shadow-sm" width="36" height="36" style="object-fit:cover;">
                <span class="d-none d-lg-inline text-dark fw-medium text-truncate" style="max-width:10rem;">{{ $u->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1" style="min-width:12rem;">
                <li class="px-3 py-2 border-bottom">
                    <div class="small text-muted text-truncate">{{ $u->email }}</div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-circle me-2 text-primary"></i>{{ __('nav.profile') }}</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i>{{ __('nav.logout') }}
                    </a>
                    <form id="logout-form-nav" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </li>
            </ul>
        </li>
        @endauth
    </ul>
</nav>

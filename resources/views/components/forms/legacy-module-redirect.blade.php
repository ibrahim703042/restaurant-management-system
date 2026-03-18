@props([
    'permission',
    'href',
    'moduleName' => '',
])
@can($permission)
<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3"><i class="fas fa-arrow-right fa-lg"></i></div>
            <div>
                <h4 class="mb-2">{{ __('forms.migrated_title') }}</h4>
                <p class="text-muted mb-0">{{ __('forms.migrated_body') }}</p>
            </div>
        </div>
        @if($moduleName)
        <p class="fw-semibold text-dark mb-2">{{ $moduleName }}</p>
        @endif
        <a href="{{ $href }}" class="btn btn-primary btn-lg rounded-pill px-4">
            <i class="fas fa-external-link-alt me-2"></i>{{ __('forms.open_module') }}
        </a>
        <p class="small text-muted mt-4 mb-0">
            <i class="fas fa-shield-alt me-1"></i>
            <code class="small">web</code> → EncryptCookies, StartSession, <strong>SetLocale</strong>, VerifyCsrfToken, <strong>AuditUserActions</strong>
            + <code class="small">auth</code> + <code class="small">permission:{{ $permission }}</code>
        </p>
    </div>
</div>
@endcan
@cannot($permission)
<div class="alert alert-warning shadow-sm d-flex align-items-center gap-2">
    <i class="fas fa-lock fa-lg"></i>
    <div>{{ __('forms.no_permission') }} <span class="badge bg-secondary">{{ $permission }}</span></div>
</div>
@endcannot

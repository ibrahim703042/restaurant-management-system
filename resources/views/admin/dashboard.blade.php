@push('styles')
<style>
    .dash-hero-stat {
        border-radius: 1rem;
        border: none;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .dash-hero-stat:hover { transform: translateY(-2px); box-shadow: 0 .75rem 2rem rgba(0,0,0,.08) !important; }
    .dash-hero-stat .stat-inner { padding: 1.35rem 1.5rem; position: relative; }
    .dash-hero-stat .stat-icon {
        width: 3rem; height: 3rem; border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; opacity: .95;
    }
    .dash-hero-stat .stat-value { font-size: 1.65rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.2; }
    .dash-hero-stat .stat-label { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
    .dash-pay-row:hover { background: rgba(60, 141, 188, .04); }
    .dash-pay-avatar { width: 38px; height: 38px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
</style>
@endpush

<div class="container-fluid px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1">{{ __('dashboard.title') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item active">{{ __('dashboard.breadcrumb') }}</li>
                </ol>
            </nav>
        </div>
        @if (Route::has('pos.index') && auth()->user()->can('sales.pos.use'))
        <a href="{{ route('pos.index') }}" class="btn btn-warning btn-lg shadow-sm px-4 rounded-pill">
            <i class="fas fa-cash-register me-2"></i>{{ __('dashboard.open_pos') }}
        </a>
        @endif
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card dash-hero-stat shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #3c8dbc 0%, #2a6f94 100%);">
                <div class="stat-inner">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label mb-1">{{ __('dashboard.income_today') }}</div>
                            <div class="stat-value">{{ number_format($stats['income_today'], 0) }}</div>
                            @if(($stats['payments_today_count'] ?? 0) > 0)
                            <div class="small mt-2 opacity-75">{{ $stats['payments_today_count'] }} {{ __('dashboard.txn_today') }}</div>
                            @endif
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25 text-white"><i class="fas fa-coins"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card dash-hero-stat shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #00a65a 0%, #007a43 100%);">
                <div class="stat-inner">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label mb-1">{{ __('dashboard.income_week') }}</div>
                            <div class="stat-value">{{ number_format($stats['income_week'], 0) }}</div>
                            @if($stats['week_trend_pct'] !== null)
                            <div class="small mt-2 {{ $stats['week_trend_pct'] >= 0 ? 'text-white' : 'text-warning' }}">
                                <i class="fas fa-arrow-{{ $stats['week_trend_pct'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($stats['week_trend_pct']) }}% {{ __('dashboard.vs_last_week') }}
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25 text-white"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card dash-hero-stat shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #605ca8 0%, #454280 100%);">
                <div class="stat-inner">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label mb-1">{{ __('dashboard.income_month') }}</div>
                            <div class="stat-value">{{ number_format($stats['income_month'], 0) }}</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25 text-white"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card dash-hero-stat shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #dd4b39 0%, #b03a2b 100%);">
                <div class="stat-inner">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label mb-1">{{ __('dashboard.client_debt') }}</div>
                            <div class="stat-value">{{ number_format($stats['unpaid_debts'], 0) }}</div>
                        </div>
                        <div class="stat-icon bg-white bg-opacity-25 text-white"><i class="fas fa-exclamation-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3"><i class="fas fa-receipt fa-lg"></i></div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('dashboard.total_orders') }}</div>
                        <div class="h3 mb-0 fw-bold">{{ number_format($stats['orders_total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3"><i class="fas fa-user-friends fa-lg"></i></div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('dashboard.clients') }}</div>
                        <div class="h3 mb-0 fw-bold">{{ number_format($stats['clients_total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3 bg-dark text-white">
                <div class="card-body d-flex flex-column justify-content-center p-4 text-center">
                    <i class="fas fa-bolt fa-2x text-warning mb-2 opacity-90"></i>
                    <div class="fw-semibold">{{ __('dashboard.recent_payments') }}</div>
                    <div class="small opacity-75">{{ __('dashboard.live_hint') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold"><i class="fas fa-history text-primary me-2"></i>{{ __('dashboard.recent_payments') }}</div>
            @can('sales.payments.manage')
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">{{ __('dashboard.view_all') }}</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-uppercase text-muted">
                        <th class="ps-4 py-3">{{ __('dashboard.when') }}</th>
                        <th class="py-3">{{ __('dashboard.bill') }}</th>
                        <th class="py-3">{{ __('dashboard.amount') }}</th>
                        <th class="py-3">{{ __('dashboard.method') }}</th>
                        <th class="py-3">{{ __('dashboard.client') }}</th>
                        <th class="pe-4 py-3">{{ __('dashboard.by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPayments as $p)
                    <tr class="dash-pay-row border-bottom border-light">
                        <td class="ps-4 text-nowrap">
                            <span class="fw-medium">{{ $p->created_at->format('M j, H:i') }}</span>
                        </td>
                        <td>
                            @if($p->bill_id && Route::has('bills.show'))
                            <a href="{{ route('bills.show', $p->bill_id) }}" class="text-decoration-none fw-semibold text-primary">{{ $p->bill->bill_number ?? '#'.$p->bill_id }}</a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="fw-bold">{{ number_format($p->amount, 0) }}</span></td>
                        <td><span class="badge bg-light text-dark border">{{ $p->payment_method }}</span></td>
                        <td>{{ $p->client?->name ?? '—' }}</td>
                        <td class="pe-4">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $p->user?->avatarUrl() ?? '' }}" alt="" class="dash-pay-avatar" width="38" height="38">
                                <span class="small fw-medium text-truncate" style="max-width:8rem;">{{ $p->user?->name ?? '—' }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            {{ __('dashboard.empty_payments') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

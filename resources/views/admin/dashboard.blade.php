<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Overview</li>
    </ol>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small">Today income</div>
                    <h3 class="mb-0">{{ number_format($stats['income_today'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small">This week</div>
                    <h3 class="mb-0">{{ number_format($stats['income_week'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body">
                    <div class="text-muted small">This month</div>
                    <h3 class="mb-0">{{ number_format($stats['income_month'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="text-muted small">Client debt total</div>
                    <h3 class="mb-0">{{ number_format($stats['unpaid_debts'], 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body text-center">
                <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                <h4>{{ $stats['orders_total'] }}</h4>
                <span class="text-muted">Total orders</span>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body text-center">
                <i class="fas fa-user-friends fa-2x text-success mb-2"></i>
                <h4>{{ $stats['clients_total'] }}</h4>
                <span class="text-muted">Clients</span>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body text-center">
                <a href="{{ route('pos.index') }}" class="btn btn-warning btn-lg w-100"><i class="fas fa-cash-register me-2"></i>Open POS</a>
            </div></div>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header"><i class="fas fa-history me-1"></i> Recent payments</div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>When</th><th>Amount</th><th>Method</th><th>Client</th><th>By</th></tr></thead>
                <tbody>
                    @forelse ($recentPayments as $p)
                    <tr>
                        <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ number_format($p->amount, 0) }}</td>
                        <td>{{ $p->payment_method }}</td>
                        <td>{{ $p->client?->name ?? '—' }}</td>
                        <td>{{ $p->user->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No payments yet. Complete a sale in POS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

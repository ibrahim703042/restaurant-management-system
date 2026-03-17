<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Overview</li>
    </ol>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-admin.stat-card variant="stripe" label="Today income" :value="number_format($stats['income_today'], 0)" accent="primary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-admin.stat-card variant="stripe" label="This week" :value="number_format($stats['income_week'], 0)" accent="success" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-admin.stat-card variant="stripe" label="This month" :value="number_format($stats['income_month'], 0)" accent="purple" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-admin.stat-card variant="stripe" label="Client debt total" :value="number_format($stats['unpaid_debts'], 0)" accent="danger" />
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <x-admin.stat-card variant="icon" icon="fas fa-receipt" iconClass="text-primary" :value="$stats['orders_total']" label="Total orders" />
        </div>
        <div class="col-md-4">
            <x-admin.stat-card variant="icon" icon="fas fa-user-friends" iconClass="text-success" :value="$stats['clients_total']" label="Clients" />
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0 admin-stat-card admin-stat-card--cta">
                <div class="card-body d-flex align-items-center justify-content-center py-4">
                    <a href="{{ route('pos.index') }}" class="btn btn-warning btn-lg w-100"><i class="fas fa-cash-register me-2"></i>Open POS</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header"><i class="fas fa-history me-1"></i> Recent payments</div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-admin mb-0">
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

@php
    $mod = fn ($key) => \App\Models\Setting::isModuleActive($key);
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.index') }}" class="brand-link">
        <span class="brand-text">{{ config('app.name', 'Restaurant') }}</span>
    </a>
    <div class="sidebar">
        @can('sales.dashboard.view')
        @if($mod('module_reports'))
        <a class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}" href="{{ route('admin.index') }}"><i class="fas fa-tachometer-alt nav-icon-width"></i> Dashboard</a>
        @endif
        @endcan
        @can('sales.pos.use')
        @if($mod('module_pos'))
        <a class="nav-link" href="{{ route('pos.index') }}"><i class="fas fa-cash-register nav-icon-width"></i> POS</a>
        @endif
        @endcan
        @can('sales.orders.manage')
        <a class="nav-link" href="{{ route('orders.index') }}"><i class="fas fa-receipt nav-icon-width"></i> Orders</a>
        @endcan
        @can('sales.bills.manage')
        <a class="nav-link" href="{{ route('bills.index') }}"><i class="fas fa-file-invoice-dollar nav-icon-width"></i> Bills</a>
        @endcan
        @can('sales.payments.manage')
        <a class="nav-link" href="{{ route('payments.index') }}"><i class="fas fa-money-bill-wave nav-icon-width"></i> Payments</a>
        @endcan
        @can('sales.debts.manage')
        @if($mod('module_debts'))
        <a class="nav-link" href="{{ route('debts.index') }}"><i class="fas fa-hand-holding-usd nav-icon-width"></i> Debts</a>
        @endif
        @endcan
        @can('sales.clients.manage')
        <a class="nav-link" href="{{ route('clients.index') }}"><i class="fas fa-user-friends nav-icon-width"></i> Clients</a>
        @endcan

        @if($mod('module_inventory'))
        @can('inventory.stock.view')
        <div class="sidebar-heading">Inventory</div>
        <a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-warehouse nav-icon-width"></i> Stock</a>
        @endcan
        @endif

        @if($mod('module_hr_payroll') || $mod('module_hr_employees'))
        <div class="sidebar-heading">HR</div>
        @can('hr.employees.manage')
        <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}"><i class="fas fa-users nav-icon-width"></i> Employees</a>
        @endcan
        @can('hr.positions.manage')
        <a class="nav-link" href="{{ route('positions.index') }}"><i class="fas fa-briefcase nav-icon-width"></i> Positions</a>
        @endcan
        @can('hr.payroll.view')
        @if($mod('module_hr_payroll'))
        <a class="nav-link" href="{{ route('payroll.index') }}"><i class="fas fa-file-invoice nav-icon-width"></i> Payroll</a>
        @endif
        @endcan
        @endif

        <div class="sidebar-heading">Menu &amp; floor</div>
        @can('ops.menu.categories.manage')
        <a class="nav-link" href="{{ route('categories.index') }}"><i class="fas fa-th nav-icon-width"></i> Categories</a>
        @endcan
        @can('ops.menu.products.manage')
        <a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-utensils nav-icon-width"></i> Products</a>
        @endcan
        @can('ops.stores.manage')
        <a class="nav-link" href="{{ route('stores.index') }}"><i class="fas fa-store nav-icon-width"></i> Stores</a>
        @endcan
        @can('ops.dining_zones.manage')
        @if($mod('module_dining_zones'))
        <a class="nav-link" href="{{ route('dining-zones.index') }}"><i class="fas fa-map-marker-alt nav-icon-width"></i> Dining zones</a>
        @endif
        @endcan
        @can('ops.dining_tables.manage')
        <a class="nav-link" href="{{ route('tables.index') }}"><i class="fas fa-chair nav-icon-width"></i> Dining tables</a>
        @endcan

        <div class="sidebar-heading">Admin</div>
        @can('admin.settings.manage')
        <a class="nav-link" href="{{ route('settings.index') }}"><i class="fas fa-sliders-h nav-icon-width"></i> Feature config</a>
        @endcan
        @can('admin.users.manage')
        @if($mod('module_user_management'))
        <a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-users-cog nav-icon-width"></i> Users</a>
        @endif
        @endcan
        @can('admin.audit.view')
        <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><i class="fas fa-history nav-icon-width"></i> Audit log</a>
        @endcan
    </div>
</aside>
<style>.nav-icon-width { width: 1.5rem; text-align: center; }</style>

@php
    $mod = fn ($key) => \App\Models\Setting::isModuleActive($key);
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <a href="{{ route('admin.index') }}" class="brand-link">
        <span class="sidebar-brand-mark">Rg</span>
        <span class="brand-text">Bar-restaurant</span>
    </a>
    <div class="sidebar">
        @can('sales.dashboard.view')
        @if($mod('module_reports'))
        <a class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}" href="{{ route('admin.index') }}"><i class="fas fa-tachometer-alt nav-icon-width"></i> {{ __('sidebar.dashboard') }}</a>
        @endif
        @endcan
        @can('sales.pos.use')
        @if($mod('module_pos'))
        <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}"><i class="fas fa-cash-register nav-icon-width"></i> {{ __('sidebar.pos') }}</a>
        @endif
        @endcan
        @can('sales.orders.manage')
        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"><i class="fas fa-receipt nav-icon-width"></i> {{ __('sidebar.orders') }}</a>
        @endcan
        @can('sales.bills.manage')
        <a class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}" href="{{ route('bills.index') }}"><i class="fas fa-file-invoice-dollar nav-icon-width"></i> {{ __('sidebar.bills') }}</a>
        @endcan
        @can('sales.payments.manage')
        <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}"><i class="fas fa-money-bill-wave nav-icon-width"></i> {{ __('sidebar.payments') }}</a>
        @endcan
        @can('sales.debts.manage')
        @if($mod('module_debts'))
        <a class="nav-link {{ request()->routeIs('debts.*') ? 'active' : '' }}" href="{{ route('debts.index') }}"><i class="fas fa-hand-holding-usd nav-icon-width"></i> {{ __('sidebar.debts') }}</a>
        @endif
        @endcan
        @can('sales.clients.manage')
        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}"><i class="fas fa-user-friends nav-icon-width"></i> {{ __('sidebar.clients') }}</a>
        @endcan

        @if($mod('module_inventory'))
        @can('inventory.stock.view')
        <div class="sidebar-heading">{{ __('sidebar.inventory') }}</div>
        <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}"><i class="fas fa-warehouse nav-icon-width"></i> {{ __('sidebar.stock') }}</a>
        @endcan
        @endif

        @if($mod('module_hr_payroll') || $mod('module_hr_employees'))
        <div class="sidebar-heading">{{ __('sidebar.hr') }}</div>
        @can('hr.employees.manage')
        <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}"><i class="fas fa-users nav-icon-width"></i> {{ __('sidebar.employees') }}</a>
        @endcan
        @can('hr.positions.manage')
        <a class="nav-link {{ request()->routeIs('positions.*') ? 'active' : '' }}" href="{{ route('positions.index') }}"><i class="fas fa-briefcase nav-icon-width"></i> {{ __('sidebar.positions') }}</a>
        @endcan
        @can('hr.payroll.view')
        @if($mod('module_hr_payroll'))
        <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}" href="{{ route('payroll.index') }}"><i class="fas fa-file-invoice nav-icon-width"></i> {{ __('sidebar.payroll') }}</a>
        @endif
        @endcan
        @can('hr.shifts.manage')
        <a class="nav-link {{ request()->routeIs('work-shifts.*') ? 'active' : '' }}" href="{{ route('work-shifts.index') }}"><i class="fas fa-calendar-alt nav-icon-width"></i> {{ __('sidebar.work_shifts') }}</a>
        @endcan
        @can('hr.leaves.manage')
        <a class="nav-link {{ request()->routeIs('employee-leaves.*') ? 'active' : '' }}" href="{{ route('employee-leaves.index') }}"><i class="fas fa-plane-departure nav-icon-width"></i> {{ __('sidebar.leaves') }}</a>
        @endcan
        @can('hr.performance.manage')
        <a class="nav-link {{ request()->routeIs('waiter-performance.*') ? 'active' : '' }}" href="{{ route('waiter-performance.index') }}"><i class="fas fa-star nav-icon-width"></i> {{ __('sidebar.waiter_performance') }}</a>
        @endcan
        @can('hr.activity.view')
        <a class="nav-link {{ request()->routeIs('employee-activities.*') ? 'active' : '' }}" href="{{ route('employee-activities.index') }}"><i class="fas fa-running nav-icon-width"></i> {{ __('sidebar.staff_activity') }}</a>
        @endcan
        @endif

        <div class="sidebar-heading">{{ __('sidebar.menu_floor') }}</div>
        @can('ops.menu.categories.manage')
        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="fas fa-th nav-icon-width"></i> {{ __('sidebar.categories') }}</a>
        @endcan
        @can('ops.menu.products.manage')
        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="fas fa-utensils nav-icon-width"></i> {{ __('sidebar.products') }}</a>
        @endcan
        @can('ops.stores.manage')
        <a class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}" href="{{ route('stores.index') }}"><i class="fas fa-store nav-icon-width"></i> {{ __('sidebar.stores') }}</a>
        @endcan
        @can('ops.dining_zones.manage')
        @if($mod('module_dining_zones'))
        <a class="nav-link {{ request()->routeIs('dining-zones.*') ? 'active' : '' }}" href="{{ route('dining-zones.index') }}"><i class="fas fa-map-marker-alt nav-icon-width"></i> {{ __('sidebar.dining_zones') }}</a>
        @endif
        @endcan
        @can('ops.dining_tables.manage')
        <a class="nav-link {{ request()->routeIs('tables.*') ? 'active' : '' }}" href="{{ route('tables.index') }}"><i class="fas fa-chair nav-icon-width"></i> {{ __('sidebar.dining_tables') }}</a>
        @endcan

        <div class="sidebar-heading">{{ __('sidebar.admin') }}</div>
        @can('admin.settings.manage')
        <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="fas fa-sliders-h nav-icon-width"></i> {{ __('sidebar.feature_config') }}</a>
        @endcan
        @can('admin.users.manage')
        @if($mod('module_user_management'))
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="fas fa-users-cog nav-icon-width"></i> {{ __('sidebar.users') }}</a>
        @endif
        @endcan
        @can('admin.audit.view')
        <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><i class="fas fa-history nav-icon-width"></i> {{ __('sidebar.audit_log') }}</a>
        @endcan
    </div>
</aside>
<style>.nav-icon-width { width: 1.5rem; text-align: center; }</style>

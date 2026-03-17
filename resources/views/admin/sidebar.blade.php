@php
    $mod = fn ($key) => \App\Models\Setting::isModuleActive($key);
@endphp
<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion" style="background: linear-gradient(180deg, #1e2433 0%, #141820 100%);">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading text-uppercase small text-secondary">Sales</div>
            @can('sales.dashboard.view')
            @if($mod('module_reports'))
            <a class="nav-link text-white-50" href="{{ route('admin.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt text-info"></i></div>Dashboard</a>
            @endif
            @endcan
            @can('sales.pos.use')
            @if($mod('module_pos'))
            <a class="nav-link text-white-50" href="{{ route('pos.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-cash-register text-warning"></i></div>POS</a>
            @endif
            @endcan
            @can('sales.orders.manage')
            <a class="nav-link text-white-50" href="{{ route('orders.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>Orders</a>
            @endcan
            @can('sales.bills.manage')
            <a class="nav-link text-white-50" href="{{ route('bills.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>Bills</a>
            @endcan
            @can('sales.qr.verify')
            @if($mod('module_qr_verify'))
            <a class="nav-link text-white-50 small" href="{{ route('bills.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-qrcode"></i></div>QR: open from bill</a>
            @endif
            @endcan
            @can('sales.payments.manage')
            <a class="nav-link text-white-50" href="{{ route('payments.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>Payments</a>
            @endcan
            @can('sales.debts.manage')
            @if($mod('module_debts'))
            <a class="nav-link text-white-50" href="{{ route('debts.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>Debts</a>
            @endif
            @endcan
            @can('sales.clients.manage')
            <a class="nav-link text-white-50" href="{{ route('clients.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>Clients</a>
            @endcan

            @if($mod('module_inventory'))
            <div class="sb-sidenav-menu-heading text-uppercase small text-secondary">Inventory</div>
            @can('inventory.stock.view')
            <a class="nav-link text-white-50" href="{{ route('inventory.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>Stock</a>
            @endcan
            @endif

            @if($mod('module_hr_payroll') || $mod('module_hr_employees'))
            <div class="sb-sidenav-menu-heading text-uppercase small text-secondary">HR &amp; payroll</div>
            @can('hr.employees.manage')
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navHrEmp" href="#"><div class="sb-nav-link-icon"><i class="fas fa-id-badge"></i></div>Employees<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navHrEmp" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('employee.create') }}">Add</a><a class="nav-link" href="{{ route('employees.index') }}">Manage</a></nav></div>
            @endcan
            @can('hr.positions.manage')
            <a class="nav-link text-white-50" href="{{ route('positions.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>Positions</a>
            @endcan
            @can('hr.payroll.view')
            @if($mod('module_hr_payroll'))
            <a class="nav-link text-white-50" href="{{ route('payroll.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-file-invoice"></i></div>Payroll</a>
            @endif
            @endcan
            @endif

            <div class="sb-sidenav-menu-heading text-uppercase small text-secondary">Menu &amp; floor</div>
            @can('ops.menu.categories.manage')
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navCat" href="#"><div class="sb-nav-link-icon"><i class="fas fa-th"></i></div>Categories<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navCat" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('category.create') }}">Add</a><a class="nav-link" href="{{ route('categories.index') }}">Manage</a></nav></div>
            @endcan
            @can('ops.menu.products.manage')
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navProd" href="#"><div class="sb-nav-link-icon"><i class="fas fa-utensils"></i></div>Products<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navProd" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('product.create') }}">Add</a><a class="nav-link" href="{{ route('products.index') }}">Manage</a></nav></div>
            @endcan
            @can('ops.stores.manage')
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navStore" href="#"><div class="sb-nav-link-icon"><i class="fas fa-store"></i></div>Stores<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navStore" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('store.create') }}">Add</a><a class="nav-link" href="{{ route('stores.index') }}">Manage</a></nav></div>
            @endcan
            @can('ops.dining_tables.manage')
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navDine" href="#"><div class="sb-nav-link-icon"><i class="fas fa-chair"></i></div>Dining tables<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navDine" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('table.create') }}">Add</a><a class="nav-link" href="{{ route('tables.index') }}">Manage</a></nav></div>
            @endcan

            <div class="sb-sidenav-menu-heading text-uppercase small text-secondary">Administration</div>
            @can('admin.settings.manage')
            <a class="nav-link text-white-50" href="{{ route('settings.index') }}"><div class="sb-nav-link-icon"><i class="fas fa-sliders-h"></i></div>Feature config</a>
            @endcan
            @can('admin.users.manage')
            @if($mod('module_user_management'))
            <a class="nav-link collapsed text-white-50" data-bs-toggle="collapse" data-bs-target="#navUsers" href="#"><div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>Users<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></a>
            <div class="collapse" id="navUsers" data-bs-parent="#sidenavAccordion"><nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="{{ route('user.create') }}">Add</a><a class="nav-link" href="{{ route('users.index') }}">Manage</a></nav></div>
            @endif
            @endcan
        </div>
    </div>
    <div class="sb-sidenav-footer bg-dark border-top border-secondary">
        <div class="small text-muted">Signed in</div>
        <div class="text-truncate">{{ Auth::user()->name }}</div>
    </div>
</nav>

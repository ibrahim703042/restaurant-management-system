<nav class="main-header navbar navbar-expand navbar-white navbar-light bg-white border-bottom">
    <ul class="navbar-nav">
        <li class="nav-item">
            <button class="nav-link btn btn-link text-dark px-2" type="button" id="sidebarToggleBtn" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.index') }}" class="nav-link">Home</a>
        </li>
    </ul>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <i class="far fa-user me-1"></i>{{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">Logout</a>
                    <form id="logout-form-nav" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </li>
            </ul>
        </li>
    </ul>
</nav>

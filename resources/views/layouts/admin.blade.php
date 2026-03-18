<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Restaurant'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link href="{{ asset('css/restaurant-admin.css') }}?v=3" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="{{ asset('css/admin-components.css') }}?v=6" rel="stylesheet">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    @include('sweetalert::alert')
    <div class="wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        @include('admin.navbar')
        @include('admin.sidebar')
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end mb-0 bg-transparent px-0">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <section class="content">
                <div class="container-fluid">
                    @yield('main-section')
                </div>
            </section>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/admin-datatables.js') }}?v=1"></script>
    <script src="{{ asset('js/admin-tomselect.js') }}?v=1"></script>
    <script>
    (function () {
        var body = document.body;
        var overlay = document.getElementById('sidebarOverlay');
        var mobileBreak = 992;

        function isMobile() { return window.innerWidth < mobileBreak; }

        function closeMobileSidebar() {
            body.classList.remove('sidebar-open');
        }

        document.getElementById('sidebarToggleBtn')?.addEventListener('click', function () {
            if (isMobile()) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapse');
            }
        });

        overlay?.addEventListener('click', closeMobileSidebar);

        window.addEventListener('resize', function () {
            if (!isMobile()) body.classList.remove('sidebar-open');
        });
    })();
    </script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/dt-pro-bulk.js') }}?v=1"></script>
    @stack('scripts')
</body>
</html>

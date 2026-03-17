<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Login')) — {{ config('app.name', 'Restaurant') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        :root {
            --auth-accent: #ceaab4;
            --auth-accent-dark: #a67b88;
            --auth-ink: #1a1416;
            --auth-card-bg: #fffefb;storage\login.png
        }
        body.auth-shell {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            background-image: linear-gradient(155deg, rgba(26, 20, 22, 0.82) 0%, rgba(45, 32, 38, 0.78) 45%, rgba(26, 20, 22, 0.88) 100%),
                url('{{ route('auth.media', ['file' => 'back-login.png']) }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .auth-brand-mark {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--auth-accent);
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .auth-brand-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 600;
            color: var(--auth-ink);
            font-size: 1.35rem;
        }
        .auth-card {
            border: none;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 1.25rem 4rem rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(255, 255, 255, 0.06) inset;
            background: var(--auth-card-bg);
        }
        .auth-visual-wrap {
            position: relative;
            min-height: 100%;
            background: linear-gradient(180deg, #2a2225 0%, #1a1416 100%);
        }
        .auth-visual-wrap img {
            width: 100%;
            height: 100%;
            min-height: 22rem;
            object-fit: cover;
            display: block;
        }
        @media (min-width: 768px) {
            .auth-visual-wrap img { min-height: 100%; }
        }
        .auth-form-panel {
            padding: 2rem 1.75rem;
        }
        @media (min-width: 992px) {
            .auth-form-panel { padding: 2.75rem 3rem; }
        }
        .auth-form-panel .form-control:focus {
            border-color: var(--auth-accent-dark);
            box-shadow: 0 0 0 0.2rem rgba(206, 170, 180, 0.35);
        }
        .btn-auth-primary {
            background: linear-gradient(135deg, #2d2426 0%, #1a1416 100%);
            border: none;
            padding: 0.65rem 1.75rem;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .btn-auth-primary:hover {
            background: linear-gradient(135deg, #3d3436 0%, #2a2225 100%);
            transform: translateY(-1px);
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.2);
        }
        .auth-links a { color: var(--auth-accent-dark); text-decoration: none; font-weight: 500; }
        .auth-links a:hover { color: var(--auth-ink); text-decoration: underline; }
        .auth-subtitle { color: #5c5356; letter-spacing: 0.04em; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }
    </style>
    @stack('auth-styles')
</head>
<body class="auth-shell d-flex align-items-center py-4 py-md-5">
    <div class="container" style="max-width: 1100px;">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @stack('auth-scripts')
</body>
</html>

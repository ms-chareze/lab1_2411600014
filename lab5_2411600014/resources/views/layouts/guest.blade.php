<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $attributes->get('page-title', 'Login') }} - Student Grade Portal</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lab 4 Theme (public/css/custom.css) -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="login-page d-flex align-items-center justify-content-center min-vh-100">

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card shadow-lg border-0">

                    <!-- Gradient Purple Card Header (Lab 4) -->
                    <div class="card-header bg-gradient-primary text-white text-center py-4">
                        <h3 class="fw-bold mb-1">Student Grade Portal</h3>
                        <p class="small mb-0 opacity-75">{{ $attributes->get('subtitle', 'Login to your dashboard') }}</p>
                    </div>

                    <div class="card-body p-4 bg-white">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

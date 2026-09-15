<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Student Grade Portal')</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Custom Purple Theme Styling -->
    <style>
        body {
            background-color: #FFF4F4;
            color: #612D53;
            font-family: 'Figtree', sans-serif;
        }
        .navbar {
            background-color: #612D53 !important;
        }
        .navbar-brand, .nav-link {
            color: #FFF4F4 !important;
        }
        .nav-link:hover {
            color: #F9B2D7 !important;
        }
        .btn-primary {
            background-color: #A084DC;
            border-color: #A084DC;
        }
        .btn-primary:hover {
            background-color: #B983FF;
            border-color: #B983FF;
        }
        .card {
            border: 1px solid #A084DC;
            border-radius: 10px;
        }
        .card-header {
            background-color: #B983FF;
            color: white;
            font-weight: bold;
            border-top-left-radius: 9px !important;
            border-top-right-radius: 9px !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- Navigation Section -->
        <nav class="navbar navbar-expand-lg shadow-sm mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ route('students.index') }}">🎓 Student Grade Portal</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('students.index') }}">Students List</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('students.create') }}">Add Student</a>
                        </li>
                        
                        @auth
                            <li class="nav-item dropdown ms-3">
                                <span class="text-light me-2">Hello, {{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Heading / Header -->
        @isset($header)
            <header class="bg-white shadow mb-4">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Main Content Section -->
        <main class="container py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
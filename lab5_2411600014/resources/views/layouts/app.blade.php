<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Student Grade Portal')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lab 4 Theme (public/css/custom.css) -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('head')
</head>
<body>

    <!-- Top Purple Navbar (Lab 4) -->
    <nav class="navbar navbar-expand-lg bg-gradient-primary navbar-dark px-4 py-2">
        <div class="container-fluid p-0">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('dashboard') }}">Student Grade Portal</a>
            <div class="d-flex align-items-center text-white">
                <span class="badge bg-light text-dark me-3 px-2 py-1">Admin Mode</span>
                <span class="me-3 fw-semibold d-none d-sm-inline">Welcome, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm px-3">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- Left Navigation Sidebar (Lab 4) -->
            <div class="col-md-2 min-vh-100 sidebar p-3">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('dashboard') ? 'active' : 'text-muted' }}"
                       href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('students.index', 'students.show', 'students.edit') ? 'active' : 'text-muted' }}"
                       href="{{ route('students.index') }}">Student Records</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('students.create') ? 'active' : 'text-muted' }}"
                       href="{{ route('students.create') }}">Add Student</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('profile.*') ? 'active' : 'text-muted' }}"
                       href="{{ route('profile.edit') }}">Profile &amp; Settings</a>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="nav-link text-danger fw-semibold w-100 text-start border-0 bg-transparent">Logout</button>
                    </form>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="col-md-10 p-4">
                @isset($header)
                    <div class="mb-4">
                        {{ $header }}
                    </div>
                @endisset

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('status') === 'profile-updated' || session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('status') === 'profile-updated' ? 'Profile saved.' : 'Password updated.' }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

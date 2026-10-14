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

    <!-- Top Purple Navbar -->
    <nav class="navbar navbar-expand-lg bg-gradient-primary navbar-dark px-4 py-2">
        <div class="container-fluid p-0">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('dashboard') }}">Student Grade Portal</a>
            <div class="d-flex align-items-center text-white gap-3">
                <span class="badge bg-light text-dark px-2 py-1">{{ Auth::user()->is_admin ? 'Admin Mode' : 'Staff Mode' }}</span>

                {{-- Lab 6: notification bell (unread count + recent alerts) --}}
                <x-notification-bell />

                {{-- Lab 6: user dropdown with logout --}}
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile &amp; Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger fw-semibold">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- Left Navigation Sidebar -->
            <div class="col-md-2 min-vh-100 sidebar p-3">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('dashboard') ? 'active' : 'text-muted' }}"
                       href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('students.index', 'students.show', 'students.edit') ? 'active' : 'text-muted' }}"
                       href="{{ route('students.index') }}">Student Records</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('students.create') ? 'active' : 'text-muted' }}"
                       href="{{ route('students.create') }}">Add Student</a>

                    <hr class="my-2">
                    <div class="small text-uppercase text-muted fw-bold px-2 mb-1">Unit Tracking</div>

                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('transactions.index', 'transactions.show') ? 'active' : 'text-muted' }}"
                       href="{{ route('transactions.index') }}">Transactions</a>
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('transactions.create') ? 'active' : 'text-muted' }}"
                       href="{{ route('transactions.create') }}">Record Transaction</a>
                    @can('adjust', App\Models\EnrollmentTransaction::class)
                        <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('adjustments.*') ? 'active' : 'text-muted' }}"
                           href="{{ route('adjustments.create') }}">Unit Adjustment</a>
                    @endcan
                    <a class="nav-link fw-semibold mb-2 {{ request()->routeIs('reports.*') ? 'active' : 'text-muted' }}"
                       href="{{ route('reports.movement') }}">Reports</a>

                    <hr class="my-2">
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

                {{-- Flash message display area --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('error') }}
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

<x-guest-layout page-title="Login" subtitle="Login to your dashboard">
    <!-- Session Status (e.g. after password reset) -->
    @if (session('status'))
        <div class="alert alert-success py-2 px-3 mb-3 small" role="alert">{{ session('status') }}</div>
    @endif

    <!-- Validation / login errors -->
    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label small text-muted">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                   value="{{ old('email') }}" placeholder="Enter email" required autofocus autocomplete="username">
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label small text-muted">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                   placeholder="Enter password" required autocomplete="current-password">
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
            <label class="form-check-label small text-muted" for="remember_me">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Login</button>

        <div class="d-flex justify-content-between mt-3 small">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}">Create account</a>
            @endif
        </div>
    </form>
</x-guest-layout>

<x-guest-layout page-title="Forgot Password" subtitle="Reset your password">
    <p class="small text-muted mb-3">
        Forgot your password? Enter your email address and we will email you a password reset link.
    </p>

    @if (session('status'))
        <div class="alert alert-success py-2 px-3 mb-3 small" role="alert">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label small text-muted">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                   value="{{ old('email') }}" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Email Password Reset Link</button>

        <div class="text-center mt-3 small">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </form>
</x-guest-layout>

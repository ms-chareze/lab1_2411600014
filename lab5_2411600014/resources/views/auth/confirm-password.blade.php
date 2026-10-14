<x-guest-layout page-title="Confirm Password" subtitle="Secure area">
    <p class="small text-muted mb-3">
        This is a secure area of the application. Please confirm your password before continuing.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label small text-muted">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                   required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Confirm</button>
    </form>
</x-guest-layout>

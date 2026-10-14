<x-guest-layout page-title="Verify Email" subtitle="Verify your email address">
    <p class="small text-muted mb-3">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we just
        emailed to you. If you didn't receive the email, we will gladly send you another.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 px-3 mb-3 small" role="alert">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary fw-semibold">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link small">Log Out</button>
        </form>
    </div>
</x-guest-layout>

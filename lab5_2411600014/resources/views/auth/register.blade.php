<x-guest-layout page-title="Register" subtitle="Create your account">
    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label small text-muted">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                   value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small text-muted">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                   value="{{ old('email') }}" required autocomplete="username">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small text-muted">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                   required autocomplete="new-password">
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small text-muted">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Register</button>

        <div class="text-center mt-3 small">
            <a href="{{ route('login') }}">Already registered?</a>
        </div>
    </form>
</x-guest-layout>

<section>
    <h5 class="fw-bold text-danger">Delete Account</h5>
    <p class="text-muted small">
        Once your account is deleted, all of its resources and data will be permanently deleted.
        Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <button type="button" class="btn btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        Delete Account
    </button>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title fw-bold">Are you sure you want to delete your account?</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Once your account is deleted, all of its resources and data will be permanently deleted.
                            Please enter your password to confirm you would like to permanently delete your account.
                        </p>
                        <input type="password" name="password" placeholder="Password"
                               class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif">
                        @if($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new bootstrap.Modal(document.getElementById('confirmUserDeletion')).show();
                });
            </script>
        @endpush
    @endif
</section>

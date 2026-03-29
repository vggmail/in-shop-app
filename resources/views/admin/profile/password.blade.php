@extends("layouts.admin")

@section("content")
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-800 text-dark mb-1">Update Password</h2>
            <p class="text-muted mb-0">Ensure your account is using a long, random password to stay secure.</p>
        </div>
    </div>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> Password updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4" style="max-width: 600px;">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">CURRENT PASSWORD</label>
                    <input type="password" name="current_password" class="form-control form-control-lg bg-light border-0 rounded-3 @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password">
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback fw-bold">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">NEW PASSWORD</label>
                    <input type="password" name="password" class="form-control form-control-lg bg-light border-0 rounded-3 @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback fw-bold">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label small fw-bold text-muted">CONFIRM NEW PASSWORD</label>
                    <input type="password" name="password_confirmation" class="form-control form-control-lg bg-light border-0 rounded-3 @error('password_confirmation', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')
                        <div class="invalid-feedback fw-bold">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end border-top pt-4">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-5 fw-bold w-100">
                        <i class="fas fa-lock me-2"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

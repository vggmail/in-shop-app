@extends("layouts.admin")

@section("content")
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-800 text-dark mb-1">Create New Tenant</h2>
            <p class="text-muted mb-0">Launch a new store instance with its own subdomain and database</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-light shadow-sm px-4 py-2 rounded-pill">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white">
                    <h5 class="mb-0 fw-bold">Store Configuration</h5>
                    <p class="small mb-0 opacity-75">All fields are required for automated provisioning</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('super-admin.tenants.store') }}" method="POST" id="tenantForm">
                        @csrf
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">STORE NAME</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Burger Town" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">SUBDOMAIN (Desired URL)</label>
                                <div class="input-group">
                                    <input type="text" name="subdomain" class="form-control @error('subdomain') is-invalid @enderror" value="{{ old('subdomain') }}" placeholder="e.g. burgertown" required>
                                    <span class="input-group-text bg-light border-start-0">.localhost</span>
                                    @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2">EXPIRY DATE (Leave blank for no expiry)</label>
                            <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                            @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4 opacity-10">
                        <h6 class="fw-bold mb-3 text-primary">Initial Admin Account</h6>

                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted mb-2">ADMIN EMAIL</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="admin@example.com" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">PASSWORD</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">CONFIRM PASSWORD</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="send_details" id="sendDetails" checked>
                                <label class="form-check-label fw-bold small text-dark" for="sendDetails">
                                    Send login credentials to tenant via email
                                </label>
                                <div class="small text-muted ms-4">Details will include the subdomain URL, email, and password.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="disable_home_page" id="disableHomePage" value="1">
                                <label class="form-check-label fw-bold small text-dark" for="disableHomePage">
                                    Disable Storefront (Admin Only)
                                </label>
                                <div class="small text-muted ms-4">If enabled, the front-end shopping site will be disabled, allowing only backend admin access based on client requirements.</div>
                            </div>
                        </div>

                        @if($errors->has('general'))
                        <div class="alert alert-danger rounded-3 fw-bold small">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ $errors->first('general') }}
                        </div>
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold shadow-lg" id="submitBtn">
                                <i class="fas fa-rocket me-2"></i> Launch New Store
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert bg-light border-0 rounded-4 mt-4 small p-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-primary me-2"></i> What happens next?</h6>
                <p class="mb-0 text-muted">
                    When you click "Launch", the system will automatically:
                    <ol class="mt-2 text-muted">
                        <li>Create a secure entry in the central tenants directory.</li>
                        <li>Create a new dedicated MySQL database for this tenant.</li>
                        <li>Run all necessary database migrations and seeders.</li>
                        <li>Provision the first administrator account so the tenant can log in immediately.</li>
                        <li>Send a welcome email with their setup details.</li>
                    </ol>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tenantForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Provisioning Store... Please wait';
    });
</script>
@endsection

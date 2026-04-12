@extends("layouts.admin")

@section("content")
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-800 text-dark mb-1">Edit Tenant</h2>
            <p class="text-muted mb-0">Update store information for <strong>{{ $tenant->subdomain }}</strong></p>
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
                    <h5 class="mb-0 fw-bold">Update Configuration</h5>
                    <p class="small mb-0 opacity-75">Modify primary store identifier and name</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('super-admin.tenants.update', $tenant->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">STORE NAME</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tenant->name) }}" placeholder="e.g. Burger Town" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2">SUBDOMAIN (Read-only)</label>
                                <div class="input-group">
                                    <input type="text" name="subdomain" class="form-control bg-light @error('subdomain') is-invalid @enderror" value="{{ old('subdomain', $tenant->subdomain) }}" readonly>
                                    <span class="input-group-text bg-light border-start-0">.localhost</span>
                                    @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2">EXPIRY DATE (Leave blank for no expiry)</label>
                            <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', $tenant->expires_at ? $tenant->expires_at->format('Y-m-d') : '') }}">
                            @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2">STORE STATUS</label>
                            <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="isActive" value="1" {{ $tenant->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-dark" for="isActive">
                                    Active - Store is accessible to customers
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-3 fw-bold shadow-lg">
                                <i class="fas fa-save me-2"></i> Update Tenant Information
                            </button>
                            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-link text-muted fw-bold">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

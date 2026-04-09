@extends("layouts.admin")

@section("content")
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-800 text-dark mb-1">Payment Gateway Settings</h2>
            <p class="text-muted mb-0">Manage your online payment providers and credentials</p>
        </div>
    </div>

    <form action="{{ route('settings.payments.update') }}" method="POST">
        @csrf
        
        <!-- PayU Section -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-credit-card me-2"></i> PayU Gateway
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="payu_active" name="payu_active" value="1" 
                            {{ (isset($gateways['PayU']) && $gateways['PayU']->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-muted" for="payu_active">ENABLE PAYU</label>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Merchant Key</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                            <input type="text" name="payu_key" class="form-control bg-light border-0 rounded-end-3" 
                                value="{{ old('payu_key', $gateways['PayU']->settings['key'] ?? '') }}" placeholder="Enter PayU Merchant Key">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Merchant Salt</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-shield-alt text-muted"></i></span>
                            <input type="password" name="payu_salt" class="form-control bg-light border-0 rounded-end-3" 
                                value="{{ old('payu_salt', $gateways['PayU']->settings['salt'] ?? '') }}" placeholder="Enter PayU Merchant Salt">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Environment Mode</label>
                        <select name="payu_mode" class="form-select bg-light border-0 rounded-3">
                            <option value="test" {{ (old('payu_mode', $gateways['PayU']->settings['mode'] ?? 'test') == 'test') ? 'selected' : '' }}>Test Mode (Sandbox)</option>
                            <option value="live" {{ (old('payu_mode', $gateways['PayU']->settings['mode'] ?? 'test') == 'live') ? 'selected' : '' }}>Live Mode (Production)</option>
                        </select>
                        <small class="text-muted d-block mt-2">Use Test Mode for integration testing. Switch to Live when ready to accept real payments.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Future Extensibility Placeholder -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 border-dashed bg-light opacity-75">
            <div class="card-body p-4 text-center">
                <div class="py-3">
                    <i class="fas fa-plus-circle fa-2x text-muted mb-2"></i>
                    <h6 class="fw-bold text-muted">More Gateways Coming Soon</h6>
                    <p class="small text-muted mb-0">Razorpay, Stripe, and more integrations are in development.</p>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-5 fw-bold">
                <i class="fas fa-save me-2"></i> Save Payment Settings
            </button>
        </div>
    </form>
</div>

<style>
    .border-dashed { border: 2px dashed #dee2e6 !important; }
    .fw-800 { font-weight: 800; }
</style>
@endsection

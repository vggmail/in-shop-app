@extends("layouts.admin")

@section("content")
    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="fw-800 text-dark mb-1">Store / Tenant Settings</h2>
                <p class="text-muted mb-0">Configure your brand identity and branch information</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2"><i class="fas fa-image me-2"></i> Brand
                        Identity</h5>

                    <div class="row mb-5">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="mb-2">
                                @if($tenant->logo)
                                    <img src="{{ asset($tenant->logo) }}" id="logo-preview" alt="Current Logo"
                                        class="img-thumbnail rounded-3 shadow-sm bg-white"
                                        style="width: 120px; height: 120px; object-fit: contain;">
                                @else
                                    <img src="" id="logo-preview" alt="Logo Preview"
                                        class="img-thumbnail rounded-3 shadow-sm bg-white d-none"
                                        style="width: 120px; height: 120px; object-fit: contain;">
                                    <div id="logo-placeholder"
                                        class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted mx-auto shadow-sm border"
                                        style="width: 120px; height: 120px;">
                                        <i class="fas fa-store fa-3x"></i>
                                    </div>
                                @endif
                            </div>
                            <label for="logo" class="btn btn-outline-primary btn-sm rounded-pill mt-2 w-100">
                                <i class="fas fa-upload me-1"></i> Upload New Logo
                            </label>
                            <input type="file" id="logo" name="logo" class="d-none" accept="image/*">
                            <small class="text-muted d-block mt-2" style="font-size: 11px;">Recommended: 512x512px Square.
                                Max: 2MB (PNG/JPG)</small>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">BRANCH / STORE NAME</label>
                                <input type="text" name="name"
                                    class="form-control form-control-lg bg-light border-0 rounded-3"
                                    value="{{ old('name', $tenant->name) }}" placeholder="e.g. Fast Food Hub - Downtown">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">TAGLINE / SLOGAN</label>
                                <input type="text" name="tagline" class="form-control bg-light border-0 rounded-3"
                                    value="{{ old('tagline', $tenant->tagline) }}"
                                    placeholder="e.g. Fresh • Fast • Delicious">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">GST NUMBER</label>
                                <input type="text" name="gst_number" class="form-control bg-light border-0 rounded-3"
                                    value="{{ old('gst_number', $tenant->gst_number) }}" placeholder="Optional GSTIN">
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2"><i class="fas fa-map-marker-alt me-2"></i>
                        Contact & Location</h5>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">STREET ADDRESS</label>
                            <textarea name="address" rows="2" class="form-control bg-light border-0 rounded-3"
                                placeholder="Full address of the branch">{{ old('address', $tenant->address) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">CITY</label>
                            <input type="text" name="city" class="form-control bg-light border-0 rounded-3"
                                value="{{ old('city', $tenant->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">STATE</label>
                            <input type="text" name="state" class="form-control bg-light border-0 rounded-3"
                                value="{{ old('state', $tenant->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">PINCODE / ZIP</label>
                            <input type="text" name="pincode" class="form-control bg-light border-0 rounded-3"
                                value="{{ old('pincode', $tenant->pincode) }}">
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label small fw-bold text-muted">TELEPHONE NUMBER</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-phone-alt text-muted"></i></span>
                                <input type="text" name="phone" class="form-control bg-light border-0 rounded-end-3"
                                    value="{{ old('phone', $tenant->phone) }}" placeholder="Store contact number">
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label small fw-bold text-muted">WHATSAPP NUMBER (For Customer Support)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 text-success"><i
                                        class="fab fa-whatsapp"></i></span>
                                <input type="text" name="whatsapp_number" class="form-control bg-light border-0 rounded-end-3"
                                    value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" placeholder="e.g. 919876543210">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Include country code WITHOUT + (e.g., 91 for India).</small>
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label small fw-bold text-muted">UPI ID / VPA (For Payments)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-qrcode text-muted"></i></span>
                                <input type="text" name="upi_id" class="form-control bg-light border-0 rounded-end-3"
                                    value="{{ old('upi_id', $tenant->upi_id) }}" placeholder="e.g. yourname@ybl" readonly
                                    disabled>
                            </div>
                            <small class="text-danger d-block mt-1" style="font-size: 11px;">UPI ID must be requested from
                                super-admin.</small>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2 mt-5"><i class="fas fa-toggle-on me-2"></i>
                        Ordering & Delivery Options</h5>
                    <p class="text-muted small mb-4">Enable or disable ordering options for your customers. These settings
                        will reflect on the home page.</p>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light shadow-sm">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="dine_in_enabled"
                                        name="dine_in_enabled" value="1" {{ $tenant->dine_in_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="dine_in_enabled">
                                        <i class="fas fa-chair text-primary me-2"></i> Dine-In
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">Allow customers to place orders for in-store
                                    dining.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light shadow-sm">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="takeaway_enabled"
                                        name="takeaway_enabled" value="1" {{ $tenant->takeaway_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="takeaway_enabled">
                                        <i class="fas fa-shopping-bag text-warning me-2"></i> Takeaway
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">Allow customers to pick up their orders from the
                                    store.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light shadow-sm">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="home_delivery_enabled"
                                        name="home_delivery_enabled" value="1" {{ $tenant->home_delivery_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="home_delivery_enabled">
                                        <i class="fas fa-motorcycle text-danger me-2"></i> Home Delivery
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">Allow customers to request delivery to their
                                    address.</small>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2 mt-5"><i class="fas fa-credit-card me-2"></i>
                        Payment Methods</h5>
                    <p class="text-muted small mb-4">Select which payment methods you want to accept from your customers.
                    </p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light shadow-sm">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="cash_enabled"
                                        name="cash_enabled" value="1" {{ $tenant->cash_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="cash_enabled">
                                        <i class="fas fa-money-bill-wave text-success me-2"></i> Cash on Delivery / Counter
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">Allow customers to pay with cash at the time of
                                    delivery or pickup.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light shadow-sm">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="online_enabled"
                                        name="online_enabled" value="1" {{ $tenant->online_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="online_enabled">
                                        <i class="fas fa-mobile-alt text-primary me-2"></i> Online Payment (UPI/Gateway)
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">Allow customers to pay online via UPI or integrated
                                    gateways.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end border-top pt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-5 fw-bold">
                            <i class="fas fa-save me-2"></i> Save Settings
                        </button>
                    </div>
            </div>
        </div>
        </form>
    </div>
    </div>
    </div>
@endsection

@section("scripts")
    <script>
        document.getElementById('logo').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    let preview = document.getElementById('logo-preview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');

                    let placeholder = document.getElementById('logo-placeholder');
                    if (placeholder) placeholder.style.display = 'none';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
@endsection
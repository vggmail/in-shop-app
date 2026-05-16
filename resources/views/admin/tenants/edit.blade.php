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
                            <div class="form-check form-switch bg-light p-3 rounded-3 border mb-3">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="isActive" value="1" {{ $tenant->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-dark" for="isActive">
                                    Active - Store is accessible to customers
                                </label>
                            </div>
                            
                            <label class="small fw-bold text-muted mb-2">STOREFRONT ACCESS</label>
                            <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="disable_home_page" id="disableHomePage" value="1" {{ $tenant->disable_home_page ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-dark" for="disableHomePage">
                                    Disable Storefront (Admin Only)
                                </label>
                                <div class="small text-muted ms-4">If enabled, the front-end shopping site will be disabled, allowing only backend admin access.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2">FLOOR PLANS</label>
                            <div id="floorPlansContainer">
                                @php
                                    $defaultPlans = [
                                        ['name' => 'Main Hall (A/C)', 'start' => 1, 'end' => 15],
                                        ['name' => 'Outdoor (Non A/C)', 'start' => 16, 'end' => 25],
                                        ['name' => 'Bar', 'start' => 26, 'end' => 30]
                                    ];
                                    $plans = is_array($tenant->floor_plans) ? $tenant->floor_plans : (json_decode($tenant->floor_plans, true) ?: $defaultPlans);
                                @endphp
                                @foreach($plans as $i => $plan)
                                    <div class="row g-2 mb-2 floor-plan-row" @if(isset($plan['is_deleted']) && $plan['is_deleted']) style="display:none;" @endif>
                                        <input type="hidden" name="floor_plans[{{$i}}][is_deleted]" class="is-deleted-flag" value="{{ $plan['is_deleted'] ?? 0 }}">
                                        <div class="col-md-5">
                                            <input type="text" name="floor_plans[{{$i}}][name]" class="form-control" placeholder="Section Name (e.g. Main Hall)" value="{{ $plan['name'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="floor_plans[{{$i}}][start]" class="form-control" placeholder="Start Table" value="{{ $plan['start'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="floor_plans[{{$i}}][end]" class="form-control" placeholder="End Table" value="{{ $plan['end'] ?? '' }}">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger w-100" data-is-saved="true" onclick="removeFloorPlan(this)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addFloorPlan()"><i class="fas fa-plus"></i> Add Floor Plan Section</button>
                            <div class="small text-muted mt-2">Define physical sections and the table number range for each.</div>
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

<!-- Delete Floor Plan Modal -->
<div class="modal fade" id="deleteFloorPlanModal" tabindex="-1" aria-labelledby="deleteFloorPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteFloorPlanModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger opacity-50"></i>
                </div>
                <h5 class="mb-2">Delete this section?</h5>
                <p class="text-muted mb-0">Are you sure you want to delete this floor plan section? Once you save the settings, this section will be permanently removed from the store's layout.</p>
            </div>
            <div class="modal-footer border-top p-3 justify-content-center bg-light">
                <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4 rounded-pill fw-bold shadow-sm" id="confirmDeleteFloorPlanBtn">Yes, Delete Section</button>
            </div>
        </div>
    </div>
</div>

<script>
    let fpIndex = {{ isset($plans) ? max(1, count($plans)) : 1 }};
    let rowToDelete = null;

    function addFloorPlan() {
        const container = document.getElementById('floorPlansContainer');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 floor-plan-row';
        row.innerHTML = `
            <input type="hidden" name="floor_plans[${fpIndex}][is_deleted]" class="is-deleted-flag" value="0">
            <div class="col-md-5">
                <input type="text" name="floor_plans[${fpIndex}][name]" class="form-control" placeholder="Section Name (e.g. Balcony)">
            </div>
            <div class="col-md-3">
                <input type="number" name="floor_plans[${fpIndex}][start]" class="form-control" placeholder="Start Table">
            </div>
            <div class="col-md-3">
                <input type="number" name="floor_plans[${fpIndex}][end]" class="form-control" placeholder="End Table">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeFloorPlan(this)"><i class="fas fa-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
        fpIndex++;
    }

    function removeFloorPlan(btn) {
        const container = document.getElementById('floorPlansContainer');
        let visibleRows = 0;
        container.querySelectorAll('.floor-plan-row').forEach(row => {
            if (row.style.display !== 'none') visibleRows++;
        });

        if (visibleRows <= 1) {
            showToast('warning', 'Action Denied', 'You must have at least one active floor plan section.');
            return;
        }

        if (btn.hasAttribute('data-is-saved')) {
            rowToDelete = btn.closest('.floor-plan-row');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteFloorPlanModal'));
            deleteModal.show();
        } else {
            btn.closest('.floor-plan-row').remove();
        }
    }

    document.getElementById('confirmDeleteFloorPlanBtn').addEventListener('click', function() {
        if (rowToDelete) {
            // Soft delete
            let flag = rowToDelete.querySelector('.is-deleted-flag');
            if(flag) flag.value = '1';
            rowToDelete.style.display = 'none';
            rowToDelete = null;
        }
        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteFloorPlanModal'));
        if(deleteModal) {
            deleteModal.hide();
        }
    });
</script>
@endsection

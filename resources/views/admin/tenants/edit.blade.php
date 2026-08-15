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

                        @php
                            $allMenus = [
                                'dashboard'     => ['label' => 'Dashboard',         'icon' => 'fa-home',               'desc' => 'Main dashboard & analytics'],
                                'pos'           => ['label' => 'POS Screen',        'icon' => 'fa-cash-register',      'desc' => 'Point of sale terminal'],
                                'express_pos'   => ['label' => 'Express POS',       'icon' => 'fa-bolt',               'desc' => 'Quick-order express POS'],
                                'table_view'    => ['label' => 'Table View',        'icon' => 'fa-th-large',           'desc' => 'Floor & table layout'],
                                'kds'           => ['label' => 'Kitchen Display',   'icon' => 'fa-fire-alt',           'desc' => 'Live kitchen order screen'],
                                'cds'           => ['label' => 'Counter Display',   'icon' => 'fa-desktop',            'desc' => 'Customer counter screen'],
                                'catalog'       => ['label' => 'Catalog',           'icon' => 'fa-list',               'desc' => 'Menu items & categories'],
                                'orders'        => ['label' => 'Orders',            'icon' => 'fa-shopping-cart',      'desc' => 'View & manage orders'],
                                'relationships' => ['label' => 'Relationships',     'icon' => 'fa-user-friends',       'desc' => 'Customers & coupons'],
                                'inventory'     => ['label' => 'Inventory',         'icon' => 'fa-boxes',              'desc' => 'Ingredients & recipes'],
                                'bar'           => ['label' => 'Bar Console',       'icon' => 'fa-glass-martini-alt',  'desc' => 'Bar wastage & excise'],
                                'shifts'        => ['label' => 'Shift History',     'icon' => 'fa-history',            'desc' => 'Open/close shift records'],
                                'financials'    => ['label' => 'Financials',        'icon' => 'fa-file-invoice-dollar','desc' => 'Expenses, payments, reports'],
                            ];
                            // null = all enabled; otherwise check per key
                            $savedMenus = $tenant->enabled_menus; // null or array
                        @endphp

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-3 d-block">
                                <i class="fas fa-sliders-h me-1 text-primary"></i> SIDEBAR MENU PERMISSIONS
                            </label>
                            <div class="bg-light border rounded-4 p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold text-dark small">Control which menu items this store admin can access</div>
                                        <div class="text-muted" style="font-size: 11px;">System Settings is always visible. Uncheck to hide from the store's sidebar.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="toggleAllMenus(true)">
                                            <i class="fas fa-check-double me-1"></i> All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="toggleAllMenus(false)">
                                            <i class="fas fa-times me-1"></i> None
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    @foreach($allMenus as $key => $menu)
                                    @php
                                        $isChecked = is_null($savedMenus) || in_array($key, $savedMenus);
                                    @endphp
                                    <div class="col-md-6">
                                        <label class="d-flex align-items-center gap-3 p-2 rounded-3 border bg-white menu-perm-item {{ $isChecked ? 'border-success border-opacity-50' : 'border-secondary border-opacity-25' }}" style="cursor:pointer; transition: all 0.2s;">
                                            <input type="checkbox"
                                                   name="enabled_menus[]"
                                                   value="{{ $key }}"
                                                   class="form-check-input menu-perm-cb flex-shrink-0"
                                                   style="width:18px; height:18px;"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width:34px; height:34px; background: {{ $isChecked ? '#dcfce7' : '#f1f5f9' }}; transition: background 0.2s;">
                                                    <i class="fas {{ $menu['icon'] }} small" style="color: {{ $isChecked ? '#16a34a' : '#94a3b8' }};"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">{{ $menu['label'] }}</div>
                                                    <div class="text-muted" style="font-size: 10px;">{{ $menu['desc'] }}</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
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

    // Toggle all menu permission checkboxes
    function toggleAllMenus(state) {
        document.querySelectorAll('.menu-perm-cb').forEach(cb => {
            cb.checked = state;
            updateMenuItemStyle(cb);
        });
    }

    // Live visual feedback when a menu checkbox is toggled
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('menu-perm-cb')) {
            updateMenuItemStyle(e.target);
        }
    });

    function updateMenuItemStyle(cb) {
        const label   = cb.closest('.menu-perm-item');
        const iconBox = label.querySelector('[style*="width:34px"]');
        const icon    = iconBox.querySelector('i');
        if (cb.checked) {
            label.classList.remove('border-secondary');
            label.classList.add('border-success');
            iconBox.style.background = '#dcfce7';
            icon.style.color = '#16a34a';
        } else {
            label.classList.remove('border-success');
            label.classList.add('border-secondary');
            iconBox.style.background = '#f1f5f9';
            icon.style.color = '#94a3b8';
        }
    }

</script>
@endsection

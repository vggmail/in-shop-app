@extends('layouts.customer')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="mb-3">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center text-primary shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fas fa-user-circle fa-4x"></i>
                </div>
            </div>
            <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
            <p class="text-muted small mb-3"><i class="fas fa-phone-alt me-1"></i> {{ $customer->phone }}</p>
            <hr class="opacity-10">
            <div class="row g-2 text-start">
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 text-center">
                        <div class="small text-muted mb-0">Total Orders</div>
                        <div class="fw-bold h5 mb-0">{{ $customer->total_orders ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 text-center">
                        <div class="small text-muted mb-0">Total Spend</div>
                        <div class="fw-bold h5 mb-0">₹{{ number_format($customer->total_spending ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('customer.logout') }}" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i> Saved Addresses</h5>
                <button class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" onclick="openAddressModal()">
                    <i class="fas fa-plus me-1"></i> Add New
                </button>
            </div>

            <div class="row g-3" id="address-list">
                @forelse($customer->addresses as $addr)
                    <div class="col-md-6" id="addr-card-{{ $addr->id }}">
                        <div class="p-3 border rounded-4 h-100 position-relative transition-all hover-shadow {{ $addr->is_default ? 'border-primary bg-primary-subtle' : 'bg-white' }}">
                            @if($addr->is_default)
                                <span class="position-absolute top-0 end-0 mt-n2 me-3 badge bg-primary rounded-pill shadow-sm">DEFAULT</span>
                            @endif
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0"><i class="fas {{ $addr->label == 'Home' ? 'fa-home' : ($addr->label == 'Work' ? 'fa-briefcase' : 'fa-map-pin') }} me-1 text-muted"></i> {{ $addr->label }}</h6>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                        <li><a class="dropdown-item small py-2" href="javascript:void(0)" onclick="editAddress({{ json_encode($addr) }})"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                        @if(!$addr->is_default)
                                            <li><a class="dropdown-item small py-2 text-primary" href="javascript:void(0)" onclick="makeDefault({{ $addr->id }})"><i class="fas fa-check-circle me-2"></i> Make Default</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item small py-2 text-danger" href="javascript:void(0)" onclick="deleteAddress({{ $addr->id }})"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <p class="small text-dark mb-1 fw-bold">{{ $addr->street_address }}</p>
                            <p class="small text-muted mb-0">{{ $addr->city }}, {{ $addr->state }} - {{ $addr->pincode }}</p>
                            
                            @if(!$addr->is_default)
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" onchange="makeDefault({{ $addr->id }})" id="switch-{{ $addr->id }}">
                                <label class="form-check-label small fw-bold text-muted" for="switch-{{ $addr->id }}">Set as Default</label>
                            </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <img src="{{ asset('assets/img/empty-address.png') }}" class="mb-3 opacity-25" style="width: 100px;">
                        <p class="text-muted fw-bold">No saved addresses yet.</p>
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-4" onclick="openAddressModal()">Add Your First Address</button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 p-2">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addrModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addressForm">
                    <input type="hidden" id="addr_id" name="id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ADDRESS TYPE (LABEL)</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="label" id="lbl-home" value="Home" checked>
                            <label class="btn btn-outline-secondary btn-sm flex-fill py-2 rounded-3" for="lbl-home"><i class="fas fa-home me-1"></i> Home</label>
                            
                            <input type="radio" class="btn-check" name="label" id="lbl-work" value="Work">
                            <label class="btn btn-outline-secondary btn-sm flex-fill py-2 rounded-3" for="lbl-work"><i class="fas fa-briefcase me-1"></i> Work</label>
                            
                            <input type="radio" class="btn-check" name="label" id="lbl-other" value="Other">
                            <label class="btn btn-outline-secondary btn-sm flex-fill py-2 rounded-3" for="lbl-other"><i class="fas fa-map-pin me-1"></i> Other</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">STREET ADDRESS / BUILDING / FLAT</label>
                        <textarea name="street_address" id="street_address" rows="2" class="form-control border-0 bg-light rounded-3" required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">CITY</label>
                            <input type="text" name="city" id="city" class="form-control border-0 bg-light rounded-pill px-3" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">PINCODE</label>
                            <input type="text" name="pincode" id="pincode" class="form-control border-0 bg-light rounded-pill px-3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">STATE</label>
                        <select name="state" id="state" class="form-select border-0 bg-light rounded-pill px-3" required>
                            <option value="">Select State</option>
                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                            <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                            <option value="Assam">Assam</option>
                            <option value="Bihar">Bihar</option>
                            <option value="Chhattisgarh">Chhattisgarh</option>
                            <option value="Goa">Goa</option>
                            <option value="Gujarat">Gujarat</option>
                            <option value="Haryana">Haryana</option>
                            <option value="Himachal Pradesh">Himachal Pradesh</option>
                            <option value="Jharkhand">Jharkhand</option>
                            <option value="Karnataka">Karnataka</option>
                            <option value="Kerala">Kerala</option>
                            <option value="Madhya Pradesh">Madhya Pradesh</option>
                            <option value="Maharashtra">Maharashtra</option>
                            <option value="Manipur">Manipur</option>
                            <option value="Meghalaya">Meghalaya</option>
                            <option value="Mizoram">Mizoram</option>
                            <option value="Nagaland">Nagaland</option>
                            <option value="Odisha">Odisha</option>
                            <option value="Punjab">Punjab</option>
                            <option value="Rajasthan">Rajasthan</option>
                            <option value="Sikkim">Sikkim</option>
                            <option value="Tamil Nadu">Tamil Nadu</option>
                            <option value="Telangana">Telangana</option>
                            <option value="Tripura">Tripura</option>
                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                            <option value="Uttarakhand">Uttarakhand</option>
                            <option value="West Bengal">West Bengal</option>
                            <option value="Delhi">Delhi</option>
                        </select>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                        <label class="form-check-label small fw-bold text-dark" for="is_default">Set as default address</label>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-pill py-3 fw-bold shadow-lg" id="saveAddrBtn">SAVE ADDRESS</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const addrModal = new bootstrap.Modal(document.getElementById('addressModal'));
    
    function openAddressModal() {
        $('#addrModalLabel').text('Add New Address');
        $('#addr_id').val('');
        $('#addressForm')[0].reset();
        $('#lbl-home').prop('checked', true);
        addrModal.show();
    }

    function editAddress(addr) {
        $('#addrModalLabel').text('Edit Address');
        $('#addr_id').val(addr.id);
        $(`input[name="label"][value="${addr.label}"]`).prop('checked', true);
        $('#street_address').val(addr.street_address);
        $('#city').val(addr.city);
        $('#pincode').val(addr.pincode);
        $('#state').val(addr.state);
        $('#is_default').prop('checked', addr.is_default == 1);
        addrModal.show();
    }

    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#saveAddrBtn');
        btn.prop('disabled', true).text('SAVING...');
        
        $.post("{{ route('customer.address.save') }}", $(this).serialize() + "&_token={{ csrf_token() }}", function(res) {
            if(res.status) {
                location.reload();
            } else {
                alert(res.message);
                btn.prop('disabled', false).text('SAVE ADDRESS');
            }
        });
    });

    function makeDefault(id) {
        $.post("{{ url('/customer/address') }}/" + id + "/default", { _token: "{{ csrf_token() }}" }, function(res) {
            if(res.status) location.reload();
        });
    }

    function deleteAddress(id) {
        if(confirm('Are you sure you want to delete this address?')) {
            $.ajax({
                url: "{{ url('/customer/address') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if(res.status) location.reload();
                }
            });
        }
    }
</script>
<style>
    .hover-shadow:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.1); transform: translateY(-3px); }
    .transition-all { transition: all 0.3s ease; }
    .bg-primary-subtle { background-color: #e7f1ff!important; }
</style>
@endsection

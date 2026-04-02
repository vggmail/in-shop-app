<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $tenant_info->name ?? 'Fast Food Hub' }} - Fresh Burgers, Pizza & More | Order Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; --accent: #ff6b81; }
        body { font-family: "Outfit", sans-serif; background-color: #f8f9fa; color: var(--dark); overflow-x: hidden; padding-bottom: 90px; }
        .hero { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url("https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800"); background-size: cover; height: 180px; border-radius: 0 0 40px 40px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; position: relative; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; height: 75px; display: flex; align-items: center; justify-content: space-around; border-top: 1px solid #eee; border-radius: 30px 30px 0 0; box-shadow: 0 -5px 25px rgba(0,0,0,0.05); z-index: 1050; padding: 0 10px; }
        .nav-item { display: flex; flex-direction: column; align-items: center; justify-content: center; color: #999; text-decoration: none; font-size: 10px; font-weight: 600; width: 60px; height: 60px; transition: 0.3s; }
        .nav-item.active { color: var(--primary); }
        .nav-item.active i { font-size: 19px; }
        .nav-item i { font-size: 18px; margin-bottom: 2px; }
        .category-pill { background: white; border: 1px solid #eee; padding: 8px 18px; border-radius: 30px; white-space: nowrap; cursor: pointer; transition: 0.3s; font-weight: 700; color: #555; box-shadow: 0 2px 5px rgba(0,0,0,0.02); font-size: 13px; }
        .category-pill.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3); }
        .food-card { border: none; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; height: 100%; transition: 0.3s; cursor: pointer; background: white; display: flex; flex-direction: column; }
        .food-card:active { transform: scale(0.96); }
        .food-img { height: 280px; background: #fafafa; display: flex; align-items: center; justify-content: center; color: #ddd; position: relative; flex-shrink: 0; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .cart-float { position: fixed; bottom: 85px; left: 20px; right: 20px; background: var(--dark); color: white; padding: 10px 18px; border-radius: 50px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; cursor: pointer; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: none; }
        .cart-float:active { transform: scale(0.98); }
        .modal-content { border-radius: 35px; border: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .coupon-pill { background: #fff5f5; border: 1px dashed var(--primary); padding: 5px 15px; border-radius: 12px; min-width: 155px; box-shadow: 2px 2px 10px rgba(0,0,0,0.02); }
        .recent-card { width: 120px; background: white; border-radius: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #eee; text-align: center; padding: 12px; cursor: pointer; transition: 0.3s; }
        .recent-card:active { transform: scale(0.95); }
        .recent-card i { background: #fff1f2; color: var(--primary); width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-size: 18px; }
    </style>
</head>
<body>

    <div class="hero">
        @php
            $hour = now()->hour;
            if($hour < 12) $greet = "Good Morning";
            elseif($hour < 17) $greet = "What's for lunch";
            elseif($hour < 21) $greet = "Good Evening";
            else $greet = "Late Night Cravings";
            
            $name = isset($customer) ? explode(' ', $customer->name)[0] : '';
        @endphp
        <div>
            @if(isset($customer))
                <p class="small fw-bold text-uppercase mb-1" style="letter-spacing: 1px; color: var(--accent);">{{ $greet }}, {{ $name }}! What would you like to eat today? 👋</p>
            @else
                <p class="small fw-bold text-uppercase mb-1" style="letter-spacing: 1px; color: var(--accent);">{{ $greet }}! What would you like to eat today? 👋</p>
            @endif
            @if(!empty($tenant_info->logo))
                <img src="{{ asset($tenant_info->logo) }}" class="mb-2 rounded-3 shadow-sm" style="height: 70px; object-fit: contain;" alt="Logo">
            @else
                <h2 class="fw-800 fs-2 mb-0" style="letter-spacing: -1.5px;">{{ strtoupper($tenant_info->name ?? 'FAST FOOD HUB') }}</h2>
            @endif
            <p class="small opacity-75 fw-bold" style="font-size: 11px;">{{ $tenant_info->tagline ?? 'Fresh • Fast • Delicious' }}</p>
        </div>
    </div>

    <!-- Bottom App Navigation -->
    <div class="bottom-nav shadow-lg">
        <a href="{{ url('/') }}" class="nav-item active">
            <i class="fas fa-home-alt"></i>
            <span>Menu</span>
        </a>
        <a href="{{ route('customer.orders') }}" class="nav-item">
            <i class="fas fa-shopping-bag"></i>
            <span>My Orders</span>
        </a>
        @if(isset($customer))
            <a href="{{ route('customer.profile') }}" class="nav-item">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </a>
        @else
            <a href="javascript:void(0)" class="nav-item" onclick="new bootstrap.Modal(document.getElementById('loginModal')).show()">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
        @endif
    </div>

    <div class="container mt-4">
        @if(isset($recentItems) && $recentItems->count() > 0)
        <div class="mb-4">
            <label class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing: 1px;">Ready for seconds?</label>
            <div class="d-flex overflow-auto gap-3 pb-2 no-scrollbar">
                @foreach($recentItems as $ri)
                <div class="recent-card" onclick="openFoodModal({{ $ri->id }}, '{{ addslashes($ri->name) }}', '{{ addslashes($ri->description) }}', {{ $ri->price }}, {{ json_encode($ri->variants) }}, {{ json_encode($ri->extras) }}, '{{ $ri->image }}', '{{ addslashes($ri->default_size) }}')">
                    <div class="bg-light rounded-4 mb-2 overflow-hidden mx-auto d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        @if($ri->image)
                            <img src="{{ asset('storage/'.$ri->image) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <i class="fas fa-history text-primary"></i>
                        @endif
                    </div>
                    <div class="small fw-bold text-truncate">{{ $ri->name }}</div>
                    <div class="small text-primary fw-bold">₹{{ $ri->price }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="d-flex overflow-auto gap-2 pb-3 mb-3 no-scrollbar">
            <div class="category-pill active" onclick="filterCat('all')">All Menu</div>
            @foreach($categories as $c)<div class="category-pill" onclick="filterCat('{{ Str::slug($c->name) }}')">{{ $c->name }}</div>@endforeach
        </div>

        @if(count($coupons) > 0)
        <div class="mb-4">
            <label class="small fw-bold text-muted text-uppercase mb-2">Available Offers</label>
            <div class="d-flex overflow-auto gap-3 pb-2 no-scrollbar">
                @foreach($coupons as $cpn)
                <div class="coupon-pill border border-danger border-opacity-25" onclick="copyCoupon('{{ $cpn->code }}')">
                    <div class="small fw-bold text-danger">{{ $cpn->discount_percentage }}% OFF</div>
                    <div class="fw-800" style="letter-spacing: 1px;">{{ $cpn->code }}</div>
                    <div class="text-muted" style="font-size: 9px;">Min order: ₹{{ $cpn->min_bill_amount }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="row g-3 mt-2" id="menu-grid">
            @foreach($items as $i)
            <div class="col-6 col-md-4 col-lg-3 food-item-box" data-cat="{{ Str::slug($i->category->name) }}">
                <div class="card food-card" onclick="openFoodModal({{ $i->id }}, '{{ addslashes($i->name) }}', '{{ addslashes($i->description) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }}, '{{ $i->image }}', '{{ addslashes($i->default_size) }}')">
                    <div class="food-img position-relative overflow-hidden">
                        @if($i->image)
                            <img src="{{ asset('storage/'.$i->image) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <i class="fas fa-hamburger fa-2x"></i>
                        @endif
                        <div class="position-absolute top-0 end-0 p-2">
                            @if($i->mrp && $i->mrp > $i->price)
                                <span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 10px;">{{ round((($i->mrp - $i->price) / $i->mrp) * 100) }}% OFF</span>
                            @endif
                            <span class="badge bg-success rounded-pill shadow-sm d-none" style="font-size: 8px;">HOT & FRESH</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1" style="font-size: 15px; color: var(--dark);">{{ $i->name }} {{ $i->default_size ? '- ' . $i->default_size : '' }}</h6>
                        <p class="text-muted mb-2 lh-sm" style="font-size: 11px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 30px;">{{ $i->description ?? 'Delicious recipe prepared with fresh ingredients and special authentic spices.' }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-primary fw-bold" style="font-size: 13px;">₹{{ number_format($i->price, 2) }}</span>
                                @if($i->mrp && $i->mrp > $i->price)
                                    <span class="text-muted text-decoration-line-through ms-1" style="font-size: 10px;">₹{{ number_format($i->mrp, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Minimized Floating Checkout Bar -->
    <div class="cart-float px-3" id="cart-float" data-bs-toggle="modal" data-bs-target="#checkoutModal" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fas fa-shopping-basket" style="font-size: 13px;"></i></div>
            <div class="fw-bold small lh-1"><span id="cart-count">0</span> Items <span class="mx-1 opacity-25">|</span> ₹<span id="cart-total-float">0.00</span></div>
        </div>
        <div class="fw-bold small text-uppercase d-flex align-items-center" style="letter-spacing: 1px;">CHECKOUT <i class="fas fa-arrow-right ms-2 opacity-75"></i></div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content shadow-lg"><div class="modal-body p-4">
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
        <h5 class="fw-bold mb-4">Customer Login</h5>
        <div id="login-error-tag" class="alert alert-danger py-2 small fw-bold d-none mb-3 border-0 rounded-3"></div>
        <div id="login-phone-section">
            <label class="small fw-bold text-muted mb-2" style="font-size: 11px;">MOBILE NUMBER</label>
            <input type="tel" id="login-phone" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control form-control-lg border-0 bg-light rounded-3 mb-3" style="font-size: 16px;" placeholder="10-digit number">
            <button class="btn btn-primary w-100 rounded-pill fw-bold py-2" onclick="checkPhoneExists()"><i class="fas fa-arrow-right me-2"></i> Continue</button>
        </div>
        <div id="login-pin-section" class="d-none text-center">
            <p class="text-muted small mb-1" id="pin-modal-subtitle"></p>
            <h4 class="fw-bold mb-4" id="pin-modal-title"></h4>
            <div id="setup-name-box" class="mb-3 text-start d-none">
                <label class="small fw-bold text-muted mb-2" style="font-size: 11px;">YOUR NAME</label>
                <input type="text" id="login-name" class="form-control form-control-lg border-0 bg-light rounded-3" placeholder="Enter your full name">
            </div>
            <div class="mb-3 position-relative">
                <label id="pin-label" class="small fw-bold text-muted d-block mb-2"></label>
                <input type="password" id="login-pin" maxlength="4" class="form-control form-control-lg text-center fw-bold border-0 bg-light rounded-3" style="letter-spacing: 15px; font-size: 24px;" placeholder="****">
                <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 mt-2 text-muted border-0 shadow-none" onclick="togglePinVisibility('login-pin', 'pin-eye-icon')">
                    <i class="fas fa-eye" id="pin-eye-icon"></i>
                </button>
            </div>
            <div id="confirm-pin-box" class="mb-3 position-relative d-none">
                <label class="small fw-bold text-muted d-block mb-2">CONFIRM PIN</label>
                <input type="password" id="login-pin-confirm" maxlength="4" class="form-control form-control-lg text-center fw-bold border-0 bg-light rounded-3" style="letter-spacing: 15px; font-size: 24px;" placeholder="****">
                <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 mt-2 text-muted border-0 shadow-none" onclick="togglePinVisibility('login-pin-confirm', 'pin-eye-icon-confirm')">
                    <i class="fas fa-eye" id="pin-eye-icon-confirm"></i>
                </button>
            </div>
            <button class="btn btn-primary w-100 rounded-pill fw-bold py-2" id="login-submit-btn" onclick="processPinLogin()"><i class="fas fa-lock-open me-2"></i> Verify & Login</button>
            <button class="btn btn-link btn-sm mt-3 text-muted text-decoration-none" onclick="goBackToLoginPhone()">Back</button>
        </div>
    </div></div></div></div>

    <!-- Food Modal -->
    <div class="modal fade" id="foodModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
        <div id="m-image-box" class="mb-3 rounded-4 overflow-hidden shadow-sm d-none" style="height: 180px;">
            <img src="" id="m-food-image" class="w-100 h-100 object-fit-cover">
        </div>
        <h4 class="fw-800 mb-1" id="m-food-name" style="letter-spacing: -0.5px;">Food Name</h4>
        <div class="bg-light p-3 rounded-4 mb-4 border-start border-primary border-4">
            <p class="text-muted small mb-0 fw-bold" id="m-food-desc" style="line-height: 1.5; font-size: 12px;"></p>
        </div>
        <div id="m-variants-box" class="mb-4 d-none">
            <label class="fw-bold small text-uppercase text-muted mb-2">Select Variant</label>
            <div id="m-variants-list" class="d-grid gap-2"></div>
        </div>
        <div id="m-extras-box" class="mb-4 d-none">
            <label class="fw-bold small text-uppercase text-muted mb-2">Extra Toppings</label>
            <div id="m-extras-list" class="row g-2"></div>
        </div>
        <div class="d-grid mt-4">
            <button class="btn btn-danger rounded-pill fw-bold py-2 px-4" onclick="addToCart()"><i class="fas fa-plus-circle me-2"></i> Add to Cart - ₹<span id="m-total">0.00</span></button>
        </div>
    </div></div></div></div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-body p-4">
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
        <h4 class="fw-bold mb-4"><i class="fas fa-shopping-basket text-danger me-2"></i> Finalize Order</h4>
        <div id="checkout-list" class="mb-4 bg-light p-3 rounded-4" style="max-height: 250px; overflow-y: auto;"></div>
        
        @if(!isset($customer))
        <div class="row g-3 mb-4">
            <div class="col-12"><label class="small fw-bold text-muted mb-1">YOUR NAME</label><input type="text" id="cust_name" class="form-control form-control-lg border-0 bg-light rounded-3" value=""></div>
            <div class="col-12"><label class="small fw-bold text-muted mb-1">MOBILE NUMBER</label><input type="tel" id="cust_phone" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control form-control-lg border-0 bg-light rounded-3" value=""></div>
        </div>
        @else
            <div class="mb-4 bg-light p-3 rounded-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-0">{{ $customer->name }}</h6>
                    <small class="text-muted">{{ $customer->phone }}</small>
                </div>
                <div class="badge bg-success rounded-pill fw-bold">VERIFIED <i class="fas fa-check-circle"></i></div>
            </div>
            <input type="hidden" id="cust_name" value="{{ $customer->name }}">
            <input type="hidden" id="cust_phone" value="{{ $customer->phone }}">
        @endif
        <div class="mb-4">
            <label class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 11px;">Order Type</label>
            @php
                $first_option = null;
                if ($tenant_info->dine_in_enabled) $first_option = 'Dine-in';
                elseif ($tenant_info->takeaway_enabled) $first_option = 'Takeaway';
                elseif ($tenant_info->home_delivery_enabled) $first_option = 'Home Delivery';
            @endphp
            <div class="btn-group w-100 shadow-sm rounded-pill overflow-hidden border">
                @if($tenant_info->dine_in_enabled)
                <input type="radio" class="btn-check" name="order_type" id="t-dine" value="Dine-in" {{ $first_option == 'Dine-in' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger py-2 border-0 fw-bold" for="t-dine"><i class="fas fa-chair me-1"></i> Dine-In</label>
                @endif

                @if($tenant_info->takeaway_enabled)
                <input type="radio" class="btn-check" name="order_type" id="t-away" value="Takeaway" {{ $first_option == 'Takeaway' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger py-2 border-0 fw-bold" for="t-away"><i class="fas fa-walking me-1"></i> Takeaway</label>
                @endif

                @if($tenant_info->home_delivery_enabled)
                <input type="radio" class="btn-check" name="order_type" id="t-delivery" value="Home Delivery" {{ $first_option == 'Home Delivery' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger py-2 border-0 fw-bold" for="t-delivery"><i class="fas fa-motorcycle me-1"></i> Delivery</label>
                @endif
            </div>
        </div>
        <div class="mb-4" id="delivery-address-box" style="display: none;">
            @if(isset($customer) && $customer->addresses->count() > 0)
                @php $default_addr = $customer->addresses->where('is_default', 1)->first() ?? $customer->addresses->first(); @endphp
                <label class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 11px;">Select Saved Address</label>
                <div class="d-flex overflow-auto gap-2 pb-2 mb-2 no-scrollbar" id="saved-addresses-list">
                    @foreach($customer->addresses as $addr)
                        <div class="category-pill py-2 px-3 border shadow-sm small text-dark address-chip {{ $addr->id == $default_addr->id ? 'active' : '' }}" onclick="setSavedAddress('{{ addslashes($addr->street_address) }}', '{{ addslashes($addr->city) }}', '{{ addslashes($addr->state) }}', '{{ $addr->pincode }}', this)">
                            <i class="fas {{ $addr->label == 'Home' ? 'fa-home' : ($addr->label == 'Work' ? 'fa-briefcase' : 'fa-map-marker-alt') }} me-1 text-danger"></i> {{ $addr->label }}
                        </div>
                    @endforeach
                    <div class="category-pill py-2 px-3 border shadow-sm small text-dark address-chip {{ !$default_addr ? 'active' : '' }}" onclick="setSavedAddress('', '', '', '', this)">
                        <i class="fas fa-plus me-1 text-primary"></i> New Address
                    </div>
                </div>
            @endif

            <div id="new-address-fields" class="{{ isset($default_addr) ? 'd-none' : '' }}">
                <div class="mb-2">
                    <input type="text" id="street_address" class="form-control border-0 bg-light rounded-4 px-4 py-3" style="font-size: 14px;" placeholder="Street Address / Building" value="{{ $default_addr->street_address ?? '' }}">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <input type="text" id="city" class="form-control border-0 bg-light rounded-pill px-4 py-2" style="font-size: 13px;" placeholder="City" value="{{ $default_addr->city ?? '' }}">
                    </div>
                    <div class="col-6">
                        <input type="text" id="pincode" class="form-control border-0 bg-light rounded-pill px-4 py-2" style="font-size: 13px;" placeholder="Pincode" value="{{ $default_addr->pincode ?? '' }}">
                    </div>
                </div>
                <div class="mb-2">
                    <select id="state" class="form-select border-0 bg-light rounded-pill px-4 py-2" style="font-size: 13px;">
                        <option value="">Select State</option>
                        @php $states = ["Andhra Pradesh","Arunachal Pradesh","Assam","Bihar","Chhattisgarh","Goa","Gujarat","Haryana","Himachal Pradesh","Jharkhand","Karnataka","Kerala","Madhya Pradesh","Maharashtra","Manipur","Meghalaya","Mizoram","Nagaland","Odisha","Punjab","Rajasthan","Sikkim","Tamil Nadu","Telangana","Tripura","Uttar Pradesh","Uttarakhand","West Bengal","Delhi"]; @endphp
                        @foreach($states as $s)
                            <option value="{{ $s }}" {{ (isset($default_addr) && $default_addr->state == $s) ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(isset($customer))
                <div class="form-check mt-2 small" id="save-address-check-box">
                    <input class="form-check-input" type="checkbox" id="save_address" value="1">
                    <label class="form-check-label text-muted fw-bold" for="save_address">Save this address</label>
                    <input type="text" id="address_label" class="form-control form-control-sm border-0 bg-light rounded-pill px-3 mt-2 d-none" style="font-size: 11px; width: 150px;" placeholder="Label (Home/Work)">
                </div>
            @endif
        </div>
        <div class="mb-4"><input type="text" id="table_number" class="form-control form-control-lg border-0 bg-light rounded-pill px-4" style="font-size: 14px;" placeholder="Table No (Optional)"></div>
        <div class="mb-4">
            <label class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 11px;">Payment Method</label>
            <div class="row g-2">
                @if($tenant_info->cash_enabled)
                <div class="col-6"><input type="radio" class="btn-check" name="payment_method" id="p-cash" value="Cash" checked><label class="btn btn-outline-success w-100 py-3 fw-bold rounded-4 shadow-sm" for="p-cash"><i class="fas fa-money-bill-wave me-2"></i> Cash</label></div>
                @endif
                
                @if($tenant_info->online_enabled)
                <div class="col-6"><input type="radio" class="btn-check" name="payment_method" id="p-upi" value="PayU" {{ !$tenant_info->cash_enabled ? 'checked' : '' }}><label class="btn btn-outline-danger w-100 py-3 fw-bold rounded-4 shadow-sm" for="p-upi"><i class="fas fa-mobile-alt me-2"></i> Online</label></div>
                @endif

                @if(!$tenant_info->cash_enabled && !$tenant_info->online_enabled)
                <div class="col-12"><div class="alert alert-danger py-2 small fw-bold">No payment methods available.</div></div>
                @endif
            </div>
        </div>
        <div class="px-2 d-flex justify-content-between mb-4 border-top pt-3 align-items-center"><h6 class="fw-bold mb-0">Total Amount</h6><h5 class="fw-bold text-success mb-0">₹<span id="checkout-total">0.00</span></h5><span id="checkout-subtotal" class="d-none">0</span></div>
        @if(!$tenant_info->dine_in_enabled && !$tenant_info->takeaway_enabled && !$tenant_info->home_delivery_enabled)
            <div class="alert alert-warning rounded-4 text-center fw-bold">
                <i class="fas fa-exclamation-triangle me-2"></i> Ordering is currently disabled for this branch.
            </div>
        @elseif(!$tenant_info->cash_enabled && !$tenant_info->online_enabled)
            <div class="alert alert-danger rounded-4 text-center fw-bold">
                <i class="fas fa-exclamation-triangle me-2"></i> No payment methods enabled. Please contact support.
            </div>
        @else
            <button class="btn btn-dark w-100 rounded-pill py-3 fw-bold shadow-lg" id="placeOrderBtn" onclick="submitOrder()"><i class="fas fa-check-circle me-2"></i> PLACE ORDER</button>
        @endif
    </div></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        let currentItem = null;
        const availableItemIds = {!! json_encode($items->pluck('id')->merge($recentItems->pluck('id'))->unique()->values()) !!};
        $(document).ready(function() { renderCart(); });
        function saveCart() { localStorage.setItem('cart', JSON.stringify(cart)); renderCart(); }
        function filterCat(slug) { $(".category-pill").removeClass("active"); $(event.target).addClass("active"); if(slug==='all') $(".food-item-box").show(); else { $(".food-item-box").hide(); $(`.food-item-box[data-cat="${slug}"]`).show(); } }
        function updateOrderTypeUI() {
            let val = $('input[name="order_type"]:checked').val();
            if(val === 'Home Delivery') { 
                $('#delivery-address-box').slideDown(); 
                $('#table_number').val('').parent().hide(); 
            } else if(val === 'Takeaway') {
                $('#delivery-address-box').slideUp();
                $('#table_number').val('').parent().hide();
            } else if(val === 'Dine-in') { 
                $('#delivery-address-box').slideUp(); 
                $('#table_number').parent().show(); 
            } else {
                $('#delivery-address-box').hide();
                $('#table_number').parent().hide();
            }
        }
        $(document).on('change', 'input[name="order_type"]', updateOrderTypeUI);
        $('#checkoutModal').on('shown.bs.modal', updateOrderTypeUI);
        $(document).on('change', '#save_address', function() { if($(this).is(':checked')) $('#address_label').removeClass('d-none'); else $('#address_label').addClass('d-none'); });
        function setSavedAddress(street, city, state, pin, el) {
            $('.address-chip').removeClass('active'); $(el).addClass('active');
            $('#street_address').val(street); $('#city').val(city); $('#state').val(state); $('#pincode').val(pin);
            if(street) { $('#save-address-check-box, #new-address-fields').addClass('d-none'); } 
            else { $('#save-address-check-box, #new-address-fields').removeClass('d-none'); }
        }
        function openFoodModal(id, name, description, price, variants, extras, image = null, defaultSize = '') {
            currentItem = { id, name, price, variants, extras, defaultSize };
            $("#m-food-name").text(name + (defaultSize ? ' - ' + defaultSize : ''));
            $("#m-food-desc").text(description || 'Fresh ingredients & special sauces.');
            $("#m-variants-list, #m-extras-list").empty();
            if(image && image !== 'null' && image !== '') {
                $("#m-image-box").removeClass("d-none");
                $("#m-food-image").attr("src", "/storage/" + image);
            } else {
                $("#m-image-box").addClass("d-none");
            }
            if(variants.length) { 
                $("#m-variants-box").removeClass("d-none"); 
                let defLabel = defaultSize || 'Standard';
                $("#m-variants-list").append(`<input type="radio" class="btn-check" name="f_var" id="v_def" value="" data-price="0" data-name="${defLabel}" checked><label class="btn btn-outline-dark rounded-pill py-2" for="v_def">${defLabel}</label>`); 
                variants.forEach(v => { $("#m-variants-list").append(`<input type="radio" class="btn-check" name="f_var" id="v_${v.id}" value="${v.id}" data-price="${v.price}" data-name="${v.name}"><label class="btn btn-outline-dark rounded-pill py-2" for="v_${v.id}">${v.name} (+₹${v.price})</label>`); }); 
            } else { $("#m-variants-box").addClass("d-none"); }
            if(extras.length) { $("#m-extras-box").removeClass("d-none"); extras.forEach(e => { $("#m-extras-list").append(`<div class="col-6"><input type="checkbox" class="btn-check m-extra-cb" id="e_${e.id}" value="${e.id}" data-price="${e.price}" data-name="${e.name}"><label class="btn btn-outline-secondary rounded-pill w-100 py-2" for="e_${e.id}">${e.name} (+₹${e.price})</label></div>`); }); } else { $("#m-extras-box").addClass("d-none"); }
            let isAvailable = availableItemIds.includes(parseInt(id));
            if(!isAvailable) {
                $("#foodModal button.btn-danger").prop("disabled", true).text("SOLD OUT");
            } else {
                $("#foodModal button.btn-danger").prop("disabled", false).html('<i class="fas fa-plus-circle me-2"></i> Add to Cart - ₹<span id="m-total">0.00</span>');
                updateMTotal();
            }
            new bootstrap.Modal(document.getElementById("foodModal")).show();
        }
        function updateMTotal() { 
            if(!currentItem) return;
            let t = parseFloat(currentItem.price || 0); 
            let v = parseFloat($("input[name='f_var']:checked").data("price") || 0); 
            let e = 0; 
            $(".m-extra-cb:checked").each(function(){ e += parseFloat($(this).data("price") || 0); }); 
            $("#m-total").text((t + v + e).toFixed(2)); 
        }
        $(document).on("change", "#foodModal .btn-check, #foodModal .m-extra-cb", updateMTotal);
        function addToCart() {
            let v = $("input[name='f_var']:checked"); let ex = []; $(".m-extra-cb:checked").each(function(){ ex.push({ id: $(this).val(), price: parseFloat($(this).data("price")), name: $(this).data("name") }); });
            let p = parseFloat(currentItem.price || 0) + parseFloat(v.data("price") || 0) + ex.reduce((a, b) => a + b.price, 0);
            let finalName = currentItem.name;
            let vName = v.data("name") || 'Standard';
            if(vName && vName !== 'Standard') { finalName += ' - ' + vName; }
            else if(currentItem.defaultSize) { finalName += ' - ' + currentItem.defaultSize; }
            cart.push({ item_id: currentItem.id, variant_id: v.val() || null, price: p, qty: 1, name: finalName, variant_name: vName, extras: ex });
            saveCart(); bootstrap.Modal.getInstance(document.getElementById("foodModal")).hide();
        }
        function renderCart() {
            let c = $("#cart-float"); let count = cart.reduce((a, b) => a + b.qty, 0); let total = cart.reduce((a, b) => a + (b.price * b.qty), 0);
            $("#cart-count").text(count); $("#cart-total-float").text(total.toFixed(2));
            count > 0 ? c.fadeIn().css("display", "flex") : c.fadeOut();
            
            let list = $("#checkout-list").empty(); 
            let hasOutOfStock = false;
            cart.forEach((item, i) => { 
                let isAvailable = availableItemIds.includes(parseInt(item.item_id));
                if(!isAvailable) hasOutOfStock = true;
                list.append(`<div class="d-flex justify-content-between mb-2 ${!isAvailable ? 'opacity-50' : ''}"><div><span class="fw-bold">${item.qty}x ${item.name}</span>${!isAvailable ? '<br><small class="text-danger fw-bold">ITEM OUT OF STOCK - REMOVE THIS</small>' : ''}<br><small class="text-muted">${item.variant_name}</small></div><div class="text-end">₹${(item.price * item.qty).toFixed(2)}<br><button class="btn btn-sm text-danger p-0" onclick="removeFromCart(${i})"><i class="fas fa-trash-alt"></i></button></div></div>`); 
            });
            $("#checkout-total, #checkout-subtotal").text(total.toFixed(2));

            if(hasOutOfStock) {
                $("#placeOrderBtn").prop("disabled", true).addClass("btn-secondary").removeClass("btn-dark").html('<i class="fas fa-exclamation-triangle me-2"></i> REMOVE OUT OF STOCK ITEMS');
            } else {
                $("#placeOrderBtn").prop("disabled", false).addClass("btn-dark").removeClass("btn-secondary").html('<i class="fas fa-check-circle me-2"></i> PLACE ORDER');
            }
        }
        function removeFromCart(i) { cart.splice(i, 1); saveCart(); }
        function submitOrder() {
            @if(!isset($customer))
                let phone = $("#cust_phone").val().trim(); let name = $("#cust_name").val().trim();
                if(!phone || !/^[6-9]\d{9}$/.test(phone)) return alert("Please enter a valid 10-digit mobile number.");
                
                // Save checkout preferences before login-reload
                localStorage.setItem('pending_name', name); 
                localStorage.setItem('checkout_after_login', 'true'); 
                localStorage.setItem('pending_order_type', $("input[name='order_type']:checked").val());
                localStorage.setItem('pending_street', $("#street_address").val() || '');
                localStorage.setItem('pending_city', $("#city").val() || '');
                localStorage.setItem('pending_state', $("#state").val() || '');
                localStorage.setItem('pending_pincode', $("#pincode").val() || '');
                localStorage.setItem('pending_table_number', $("#table_number").val());
                localStorage.setItem('pending_payment_method', $("input[name='payment_method']:checked").val());

                $("#checkoutModal").modal("hide"); 
                $("#login-phone").val(phone); 
                checkPhoneExists(); 
                $("#loginModal").modal("show"); 
                return;
            @endif
            let items = cart.map(c => ({ 
                item_id: c.item_id, 
                variant_id: c.variant_id, 
                price: c.price, 
                quantity: c.qty, 
                total: (c.price || 0) * (c.qty || 1), 
                extras: (c.extras || []).map(e => ({ id: e.id, price: e.price })) 
            }));
            $("#placeOrderBtn").prop("disabled", true).text("Processing...");
            
            let s_addr = $("#street_address").val() || '';
            let c_addr = $("#city").val() || '';
            let st_addr = $("#state").val() || '';
            let p_addr = $("#pincode").val() || '';
            let full_addr = s_addr ? (s_addr + ', ' + c_addr + ', ' + st_addr + ' - ' + p_addr) : '';

            $.post("{{ route('home.store') }}", { 
                _token: "{{ csrf_token() }}", 
                customer_name: $("#cust_name").val(), 
                customer_phone: $("#cust_phone").val(), 
                order_type: $("input[name='order_type']:checked").val() || "Takeaway", 
                payment_method: $("input[name='payment_method']:checked").val() || "Cash", 
                table_number: $("#table_number").val(), 
                delivery_address: full_addr, 
                street_address: s_addr,
                city: c_addr,
                state: st_addr,
                pincode: p_addr,
                save_address: $("#save_address").is(':checked') ? 1 : 0,
                address_label: $("#address_label").val(),
                items: JSON.stringify(items), 
                total_amount: parseFloat($("#checkout-total").text()), 
                grand_total: parseFloat($("#checkout-total").text()) 
            }, function(res) {
                if(res.status) { 
                    localStorage.removeItem('cart'); 
                    if(res.redirect_url) window.location.href = res.redirect_url; 
                    else window.location.href = "{{ url('/order') }}/" + res.order_number + "/success"; 
                } else {
                    alert(res.message || "Error placing order");
                    $("#placeOrderBtn").prop("disabled", false).text("PLACE ORDER");
                }
            }).fail(function(xhr) {
                let msg = "Error placing order";
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
                $("#placeOrderBtn").prop("disabled", false).text("PLACE ORDER");
            });
        }
        function checkPhoneExists() {
            let p = $("#login-phone").val().trim(); if(!/^[6-9]\d{9}$/.test(p)) return showLoginError("Please enter a valid 10-digit mobile number.");
            $.post("{{ route('customer.checkPhone') }}", { _token: "{{ csrf_token() }}", phone: p }, function(res) {
                if(res.status) { 
                    $("#login-phone-section").addClass("d-none"); 
                    $("#login-pin-section").removeClass("d-none"); 
                    $("#pin-modal-title").text(res.exists ? "Enter PIN" : "Setup Secure PIN"); 
                    $("#pin-modal-subtitle").text(p);
                    if(!res.exists) {
                        $("#confirm-pin-box").removeClass("d-none");
                        $("#setup-name-box").removeClass("d-none");
                        if(localStorage.getItem('pending_name')) { $("#login-name").val(localStorage.getItem('pending_name')); }
                        $("#pin-label").text("CREATE 4-DIGIT PIN");
                    } else {
                        $("#confirm-pin-box").addClass("d-none");
                        $("#setup-name-box").addClass("d-none");
                        $("#pin-label").text("ENTER PIN");
                    }
                }
            });
        }
        function processPinLogin() {
            let p = $("#login-phone").val().trim(); 
            let pin = $("#login-pin").val().trim();
            let pinConfirm = $("#login-pin-confirm").val().trim();
            let name = $("#login-name").val() ? $("#login-name").val().trim() : (localStorage.getItem('pending_name') || '');
            
            // Check if confirm box is visible
            if(!$("#confirm-pin-box").hasClass("d-none")) {
                if(!name) return showLoginError("Please enter your name!");
                if(pin !== pinConfirm) return showLoginError("PINs do not match!");
            }

            $.post("{{ route('customer.login') }}", { _token: "{{ csrf_token() }}", phone: p, pin: pin, name: name }, function(res) {
                if(res.status) { if(res.device_token) localStorage.setItem('customer_device_token', res.device_token); location.reload(); } else showLoginError(res.message || "Invalid PIN");
            }).fail(function(xhr) {
                let msg = "Invalid PIN!";
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showLoginError(msg);
                $("#login-pin, #login-pin-confirm").val(""); // Clear on fail
            });
        }
        $(document).on("keyup", "#login-pin, #login-pin-confirm", function() {
            if($(this).val().length === 4) {
                if(!$("#confirm-pin-box").hasClass("d-none")) {
                    if($("#login-pin").val().length === 4 && $("#login-pin-confirm").val().length === 4) processPinLogin();
                } else {
                    processPinLogin();
                }
            }
        });
        function goBackToLoginPhone() {
            $("#login-pin-section").addClass("d-none");
            $("#login-phone-section").removeClass("d-none");
            $("#login-pin, #login-pin-confirm, #login-name").val("");
            $("#login-error-tag").addClass("d-none");
        }
        function showLoginError(msg) {
            const err = $("#login-error-tag");
            err.text(msg).removeClass("d-none");
            setTimeout(() => { err.addClass("d-none"); }, 3000);
        }
        function togglePinVisibility(id, iconId) {
            const input = document.getElementById(id);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
            $(document).ready(function() {
                @if(session('reorder_cart')) localStorage.setItem('cart', '{!! session('reorder_cart') !!}'); cart = JSON.parse(localStorage.getItem('cart')); renderCart(); @php session()->forget('reorder_cart'); @endphp @endif
                
                // Continue checkout after login if flag is set
                if(localStorage.getItem('checkout_after_login') === 'true') {
                    localStorage.removeItem('checkout_after_login');
                    @if(isset($customer))
                        // Restore pending checkout preferences
                        let pStreet = localStorage.getItem('pending_street');
                        let pCity = localStorage.getItem('pending_city');
                        let pState = localStorage.getItem('pending_state');
                        let pPin = localStorage.getItem('pending_pincode');
                        let pTable = localStorage.getItem('pending_table_number');
                        let pPay = localStorage.getItem('pending_payment_method');
                        
                        if(pType) {
                            $(`input[name='order_type'][value='${pType}']`).prop('checked', true).change();
                            if(pType === 'Home Delivery') $('#delivery-address-box').show(); 
                        }
                        if(pStreet) $("#street_address").val(pStreet);
                        if(pCity) $("#city").val(pCity);
                        if(pState) $("#state").val(pState);
                        if(pPin) $("#pincode").val(pPin);
                        if(pTable) $("#table_number").val(pTable);
                        if(pPay) $(`input[name='payment_method'][value='${pPay}']`).prop('checked', true);

                        // Clean up
                        localStorage.removeItem('pending_order_type');
                        localStorage.removeItem('pending_street');
                        localStorage.removeItem('pending_city');
                        localStorage.removeItem('pending_state');
                        localStorage.removeItem('pending_pincode');
                        localStorage.removeItem('pending_table_number');
                        localStorage.removeItem('pending_payment_method');

                        setTimeout(() => { submitOrder(); }, 500);
                    @endif
                }

                let t = localStorage.getItem('customer_device_token'); 
            @if(!isset($customer)) if(t) $.post("{{ route('customer.autoLogin') }}", { _token: "{{ csrf_token() }}", device_token: t }, function(res){ if(res.status) location.reload(); else localStorage.removeItem('customer_device_token'); }); @endif
        });
    </script>
</body>
</html>
@extends("layouts.admin")

@section("styles")
    <style>
        /* Hide Sidebar and topbar for Express POS */
        #admin-sidebar, .sidebar-overlay, .top-navbar { display: none !important; }
        .col.main-content { margin-left: 0 !important; width: 100vw !important; max-width: 100vw !important; padding: 0 !important; }
        body { overflow-x: hidden; background: #f8fafc; }
        
        /* Light Theme Top Bar (Standard POS style but with KDS features) */
        .express-topbar { background: #fff; color: #1e293b; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; width: 100%; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .express-brand { font-weight: 800; font-size: 1.1rem; color: #1e293b; }
        .express-clock { font-size: 1.2rem; font-weight: 700; color: #ff4757; }
        .btn-exit-express { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 6px 16px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 700; }
        .btn-exit-express:hover { color: #1e293b; background: #e2e8f0; }

        .express-content-wrapper { padding: 25px; }
        
        .item-card {
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            background: #fff;
            position: relative;
            overflow: hidden;
            padding: 15px !important;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #ff4757;
        }

        .item-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.85rem;
            margin-bottom: 5px;
            height: 2.4em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-price {
            color: #ff4757;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 16px;
            color: #64748b;
            background: #fff;
            margin-right: 8px;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .nav-pills .nav-link.active {
            background-color: #ff4757;
            color: white;
            border-color: #ff4757;
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.2);
        }

        /* Cart Styling to match screenshot 2 */
        .cart-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
            background: #fff;
        }

        .order-type-pills {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .order-type-btn {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            transition: 0.2s;
        }

        .order-type-btn.active {
            background: #3b82f6;
            color: #fff;
        }

        .express-checkout-btns {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 12px;
            padding: 20px;
            background: #fff;
            border-top: 1px solid #f1f5f9;
        }

        .btn-place-order {
            background: #ff4757;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.2);
        }
        .btn-place-order:hover { background: #ff2e44; transform: translateY(-1px); }

        .payment-select {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
        }

        /* Modal styling */
        .modal-content { border-radius: 20px; border: none; }
        .variant-btn, .extra-btn { 
            border: 1px solid #f1f5f9; 
            border-radius: 10px; 
            padding: 12px; 
            text-align: left; 
            width: 100%;
            background: #f8fafc;
            font-weight: 600;
            margin-bottom: 8px;
            transition: 0.2s;
        }
        .btn-check:checked + .variant-btn { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .btn-check:checked + .extra-btn { background: #22c55e; color: #fff; border-color: #22c55e; }

        #success-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.98); z-index: 2000;
            display: none; flex-direction: column; align-items: center; justify-content: center;
        }
        
        .pulse-dot {
            animation: pulseAnim 1.8s infinite;
        }
        @keyframes pulseAnim {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
    </style>
@endsection

@section("content")
<div class="express-topbar">
    <div class="express-brand d-flex align-items-center gap-3">
        <span><i class="fas fa-bolt text-warning me-2"></i>Express POS</span>
        @if(isset($activeHappyHour))
            <span class="badge bg-danger px-3 py-2 rounded-pill pulse-dot" style="font-size: 0.75rem;">
                <i class="fas fa-glass-martini-alt me-1"></i> Happy Hour Live: {{ floatval($activeHappyHour->discount_percent) }}% OFF!
            </span>
        @endif
    </div>
    <div class="express-clock" id="express-clock">00:00:00 PM</div>
    <a href="{{ route('dashboard') }}" class="btn-exit-express"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
</div>

<div class="express-content-wrapper">
    <div class="row g-4">
    <!-- Menu Grid (Left) -->
    <div class="col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0">Menu Selection</h4>
            <div class="input-group w-50 shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-0" placeholder="Search menu..." id="expressSearch">
            </div>
        </div>

        <ul class="nav nav-pills mb-4 overflow-auto flex-nowrap pb-2" id="pills-tab">
            <li class="nav-item">
                <button class="nav-link active" onclick="filterCat('all', this)">
                    <i class="fas fa-star text-warning me-1"></i> Popular
                </button>
            </li>
            @foreach($categories as $cat)
                <li class="nav-item">
                    <button class="nav-link" onclick="filterCat('cat-{{ $cat->id }}', this)">{{ $cat->name }}</button>
                </li>
            @endforeach
        </ul>

        <div class="item-grid row g-3 row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2">
            @foreach($items as $item)
                <div class="col item-node cat-{{ $item->category_id }}" data-name="{{ strtolower($item->name) }}">
                    <div class="card item-card text-center p-3 h-100" onclick="handleItemClick({{ json_encode($item) }})">
                        <div class="bg-light rounded-3 p-3 mb-2 position-relative">
                            <i class="fas fa-star text-warning position-absolute top-0 start-0 m-2" style="font-size: 0.7rem;"></i>
                            <i class="fas fa-utensils fa-2x text-success"></i>
                        </div>
                        <div class="item-name">{{ $item->name }}</div>
                        <div class="item-price">₹{{ number_format($item->price, 0) }}</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-success" style="width: 80%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Cart Section (Right) -->
    <div class="col-md-5">
        <div class="cart-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-receipt text-muted me-2"></i>Current Order</h5>
                <button class="btn btn-sm text-danger fw-bold" onclick="clearCart()"><i class="fas fa-trash-alt me-1"></i> Clear</button>
            </div>

            <div class="p-3">
                <div class="order-type-pills">
                    <button class="order-type-btn active" onclick="setOrderType('Dine-in', this)"><i class="fas fa-chair me-1"></i> Dine In</button>
                    <button class="order-type-btn" onclick="setOrderType('Takeaway', this)"><i class="fas fa-walking me-1"></i> Takeaway</button>
                    <button class="order-type-btn" onclick="setOrderType('Home Delivery', this)"><i class="fas fa-motorcycle me-1"></i> Delivery</button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 position-relative">
                        <label class="small fw-bold text-muted text-uppercase mb-1 d-block" style="font-size: 0.6rem;">CUSTOMER (OPTIONAL)</label>
                        <div class="input-group input-group-sm rounded-pill border overflow-hidden bg-white">
                            <span class="input-group-text bg-white border-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" id="customer_phone" class="form-control border-0 shadow-none" placeholder="Phone..." autocomplete="off">
                            <button class="btn btn-outline-secondary border-0 bg-transparent d-none" type="button" id="clear_cust"><i class="fas fa-times"></i></button>
                        </div>
                        <div id="customer_dropdown" class="position-absolute w-100 bg-white shadow-lg rounded-3 border d-none" style="z-index: 1060; max-height: 200px; overflow-y: auto; top: 100%; left: 0;"></div>
                        <input type="hidden" id="selected_customer_id">
                    </div>
                    <div class="col-3">
                        <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.6rem;">TABLE</label>
                        <input type="text" id="table_number" class="form-control form-control-sm rounded-pill border text-center" placeholder="No">
                    </div>
                    <div class="col-3">
                        <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.6rem;">COUPON CODE</label>
                        <input type="text" id="coupon_code" class="form-control form-control-sm rounded-pill border text-center" placeholder="...">
                    </div>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto px-3" id="cart-list">
                <div class="text-center py-5 opacity-25">
                    <i class="fas fa-shopping-basket fa-4x mb-3"></i>
                    <p>No items in the cart yet.</p>
                </div>
            </div>

            <div class="p-3 bg-white border-top">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span class="fw-bold">Subtotal</span>
                    <span id="sub-total" class="fw-bold">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-success small">
                    <span class="fw-bold">Discount</span>
                    <span class="fw-bold">-₹0.00</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top">
                    <span class="h5 fw-bold mb-0 text-dark">Total Payable</span>
                    <span class="h4 fw-bold text-primary mb-0" id="cart-total" style="font-size: 1.8rem;">₹0.00</span>
                </div>

                <div class="express-checkout-btns">
                    <select id="payment_method" class="payment-select">
                        <option value="Cash">💵 CASH</option>
                        <option value="UPI">📱 UPI / QR</option>
                        <option value="Card">💳 CARD</option>
                    </select>
                    <button class="btn-place-order" onclick="checkout()">
                        <i class="fas fa-check-circle me-1"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customization Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalItemName">Customize Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="m_item_id">
                <input type="hidden" id="m_base_price">
                <input type="hidden" id="m_item_name">
                
                <div id="variantsBox" class="mb-4 d-none">
                    <label class="small fw-bold mb-2 text-muted text-uppercase">Variation</label>
                    <div id="m_variants_list"></div>
                </div>

                <div id="extrasBox" class="mb-4 d-none">
                    <label class="small fw-bold mb-2 text-muted text-uppercase">Add Extras</label>
                    <div id="m_extras_list" class="row g-2"></div>
                </div>

                <div class="d-flex align-items-center justify-content-between bg-light rounded-pill p-2" style="width: 150px;">
                    <button class="btn btn-dark btn-sm rounded-circle" onclick="updateModalQty(-1)"><i class="fas fa-minus"></i></button>
                    <span class="fw-bold fs-5" id="pos-m-qty">1</span>
                    <button class="btn btn-dark btn-sm rounded-circle" onclick="updateModalQty(1)"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="modal-footer border-0">
                <div class="flex-grow-1">
                    <span class="small text-muted">Total</span>
                    <h4 class="fw-bold text-success mb-0">₹<span id="m_total_price">0.00</span></h4>
                </div>
                <button class="btn btn-primary rounded-pill px-4 fw-bold" onclick="addConfiguredItem()">ADD ITEM</button>
            </div>
        </div>
    </div>
</div>

<div id="success-overlay">
    <div class="text-success" style="font-size: 5rem;"><i class="fas fa-check-circle"></i></div>
    <h1 class="fw-bold mt-3">ORDER PLACED!</h1>
    <h2 id="success-token" class="text-warning fw-900" style="font-size: 5rem;">TOKEN #000</h2>
    <p class="text-muted">Printing receipt...</p>
</div>
@endsection

@section("scripts")
<script>
    function updateClock() {
        const now = new Date();
        const clock = document.getElementById('express-clock');
        if(clock) clock.textContent = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }
    setInterval(updateClock, 1000);
    updateClock();

    let cart = [];
    let currentOrderType = 'Dine-in';
    let posModalQty = 1;
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Search
    document.getElementById('expressSearch').addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.item-node').forEach(el => {
            el.style.display = el.getAttribute('data-name').includes(val) ? 'block' : 'none';
        });
    });

    // Customer Search (Matches Standard POS)
    $("#customer_phone").on("input", function () {
        let q = $(this).val();
        if (q.length < 1) { $("#customer_dropdown").addClass("d-none"); return; }
        $.get("{{ route('customers.search') }}", { q: q }, function (data) {
            let html = "";
            if (data && Array.isArray(data)) {
                data.forEach(c => {
                    html += `<div class="p-2 border-bottom pointer cust-item" style="cursor:pointer;" 
                        onclick="selectCustomer('${c.id}', '${c.phone}')"
                        data-id="${c.id}" data-phone="${c.phone}">
                        <div class="fw-bold" style="font-size: 0.8rem; color:#1e293b;">${c.name}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">${c.phone}</div>
                    </div>`;
                });
            }
            if (html) $("#customer_dropdown").html(html).removeClass("d-none");
            else $("#customer_dropdown").addClass("d-none");
        });
    }).on("keydown", function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const firstItem = $(".cust-item").first();
            if (firstItem.length) {
                selectCustomer(firstItem.data('id'), firstItem.data('phone'));
            }
        }
    });

    window.selectCustomer = function(id, phone) {
        // 1. Immediate UI state change
        $("#selected_customer_id").val(id);
        $("#customer_phone").val(phone).prop("readonly", true);
        $("#clear_cust").removeClass("d-none");
        $("#customer_dropdown").addClass("d-none").empty().hide(); // Immediate hide
        
        // 2. Delayed cleanup and focus transition
        setTimeout(() => {
            $("#customer_dropdown").addClass("d-none").empty().hide(); // Double check hide
            $("#table_number").focus();
        }, 50);
    };

    // Hide dropdown when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest(".position-relative").length) {
            $("#customer_dropdown").addClass("d-none");
        }
    });

    $("#clear_cust").on("click", function () {
        $("#selected_customer_id").val("");
        $("#customer_phone").val("").prop("readonly", false);
        $(this).addClass("d-none");
    });

    function setOrderType(type, btn) {
        currentOrderType = type;
        document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function filterCat(catId, btn) {
        document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.item-node').forEach(el => {
            if (catId === 'all') el.style.display = 'block';
            else el.style.display = el.classList.contains(catId) ? 'block' : 'none';
        });
    }

    function handleItemClick(item) {
        if ((item.variants && item.variants.length > 0) || (item.extras && item.extras.length > 0)) {
            openItemModal(item);
        } else {
            addToCart({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: 1,
                variant_id: null,
                extras: []
            });
        }
    }

    function openItemModal(item) {
        document.getElementById('m_item_id').value = item.id;
        document.getElementById('m_item_name').value = item.name;
        document.getElementById('m_base_price').value = item.price;
        document.getElementById('modalItemName').textContent = item.name;
        posModalQty = 1;
        document.getElementById('pos-m-qty').textContent = 1;

        // Variants
        const vBox = document.getElementById('variantsBox');
        const vList = document.getElementById('m_variants_list');
        if (item.variants && item.variants.length > 0) {
            vBox.classList.remove('d-none');
            let html = `<div>
                <input type="radio" class="btn-check m_variant_sel" name="v_sel" id="v_def" value="" data-price="${item.price}" data-name="Regular" checked onchange="calcModalPrice()">
                <label class="variant-btn" for="v_def">Regular <span class="float-end">₹${item.price}</span></label>
            </div>`;
            item.variants.forEach(v => {
                html += `<div>
                    <input type="radio" class="btn-check m_variant_sel" name="v_sel" id="v_${v.id}" value="${v.id}" data-price="${v.price}" data-name="${v.name}" onchange="calcModalPrice()">
                    <label class="variant-btn" for="v_${v.id}">${v.name} <span class="float-end">₹${v.price}</span></label>
                </div>`;
            });
            vList.innerHTML = html;
        } else {
            vBox.classList.add('d-none');
        }

        // Extras
        const eBox = document.getElementById('extrasBox');
        const eList = document.getElementById('m_extras_list');
        if (item.extras && item.extras.length > 0) {
            eBox.classList.remove('d-none');
            let html = '';
            item.extras.forEach(e => {
                html += `<div class="col-6">
                    <input type="checkbox" class="btn-check m_extra_cb" id="ext_${e.id}" value="${e.id}" data-price="${e.price}" data-name="${e.name}" onchange="calcModalPrice()">
                    <label class="extra-btn" for="ext_${e.id}">${e.name} <br><span class="small">+₹${e.price}</span></label>
                </div>`;
            });
            eList.innerHTML = html;
        } else {
            eBox.classList.add('d-none');
        }

        calcModalPrice();
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    function updateModalQty(delta) {
        if (posModalQty + delta < 1) return;
        posModalQty += delta;
        document.getElementById('pos-m-qty').textContent = posModalQty;
        calcModalPrice();
    }

    function calcModalPrice() {
        const vEl = document.querySelector('.m_variant_sel:checked');
        const base = vEl ? parseFloat(vEl.getAttribute('data-price')) : parseFloat(document.getElementById('m_base_price').value);
        let extraTotal = 0;
        document.querySelectorAll('.m_extra_cb:checked').forEach(cb => extraTotal += parseFloat(cb.getAttribute('data-price')));
        document.getElementById('m_total_price').textContent = ((base + extraTotal) * posModalQty).toFixed(2);
    }

    function addConfiguredItem() {
        const id = document.getElementById('m_item_id').value;
        const name = document.getElementById('m_item_name').value;
        const vEl = document.querySelector('.m_variant_sel:checked');
        const varId = vEl ? vEl.value : null;
        const varName = vEl ? vEl.getAttribute('data-name') : '';

        const selExtras = [];
        document.querySelectorAll('.m_extra_cb:checked').forEach(cb => {
            selExtras.push({ id: cb.value, name: cb.getAttribute('data-name'), price: cb.getAttribute('data-price') });
        });

        const finalPrice = parseFloat(document.getElementById('m_total_price').textContent) / posModalQty;

        addToCart({
            id: id,
            name: name + (varName && varName !== 'Regular' ? ' (' + varName + ')' : ''),
            price: finalPrice,
            qty: posModalQty,
            variant_id: varId,
            extras: selExtras
        });

        bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
    }

    function addToCart(item) {
        const itemHash = item.id + '-' + (item.variant_id || '0') + '-' + item.extras.map(e => e.id).sort().join(',');
        const existing = cart.find(i => i.hash === itemHash);
        if (existing) existing.qty += item.qty;
        else cart.push({ ...item, hash: itemHash });
        renderCart();
    }

    function updateCartQty(hash, delta) {
        const item = cart.find(i => i.hash === hash);
        if (item) {
            item.qty += delta;
            if (item.qty <= 0) cart = cart.filter(i => i.hash !== hash);
            renderCart();
        }
    }

    function renderCart() {
        const list = document.getElementById('cart-list');
        const totalEl = document.getElementById('cart-total');
        const subTotalEl = document.getElementById('sub-total');
        
        if (cart.length === 0) {
            list.innerHTML = `<div class="text-center py-5 opacity-25"><i class="fas fa-shopping-basket fa-4x mb-3"></i><p>No items in the cart yet.</p></div>`;
            totalEl.textContent = '₹0.00';
            subTotalEl.textContent = '₹0.00';
            return;
        }

        list.innerHTML = '';
        let total = 0;
        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            list.innerHTML += `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <div class="fw-bold" style="font-size:0.85rem;">${item.name}</div>
                        <div class="small text-muted">₹${item.price} x ${item.qty}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:24px; height:24px; padding:0;" onclick="updateCartQty('${item.hash}', -1)">-</button>
                        <span class="fw-bold">${item.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width:24px; height:24px; padding:0;" onclick="updateCartQty('${item.hash}', 1)">+</button>
                        <div class="ms-3 fw-bold">₹${itemTotal.toFixed(0)}</div>
                    </div>
                </div>
            `;
        });
        totalEl.textContent = '₹' + total.toFixed(2);
        subTotalEl.textContent = '₹' + total.toFixed(2);
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function checkout() {
        const method = document.getElementById('payment_method').value;
        if (cart.length === 0) return alert('Cart is empty!');
        const total = cart.reduce((s, i) => s + (i.price * i.qty), 0);
        
        const data = {
            order_type: currentOrderType,
            table_number: document.getElementById('table_number').value,
            customer_id: document.getElementById('selected_customer_id').value,
            customer_phone: document.getElementById('customer_phone').value,
            payment_method: method,
            payment_status: 'Paid',
            total_amount: total,
            grand_total: total,
            source: 'POS',
            items: cart.map(i => ({
                item_id: i.id,
                variant_id: i.variant_id,
                price: i.price,
                quantity: i.qty,
                total: i.price * i.qty,
                extras: i.extras.map(e => ({ id: e.id, price: e.price }))
            }))
        };

        const btns = document.querySelectorAll('.btn-express');
        btns.forEach(b => b.disabled = true);

        fetch("{{ route('pos.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status) {
                showSuccess(res.data.token_number);
                const printUrl = "{{ url('cp/orders') }}/" + res.order_id + "/invoice";
                window.open(printUrl, 'POS_Invoice', 'width=450,height=700,top=50,left=50,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
            } else {
                alert('Error: ' + res.message);
                btns.forEach(b => b.disabled = false);
            }
        });
    }

    function showSuccess(token) {
        const overlay = document.getElementById('success-overlay');
        document.getElementById('success-token').textContent = 'TOKEN #' + token;
        overlay.style.display = 'flex';
        setTimeout(() => {
            overlay.style.display = 'none';
            clearCart();
            document.querySelectorAll('.btn-express').forEach(b => b.disabled = false);
        }, 2000);
    }
</script>
@endsection

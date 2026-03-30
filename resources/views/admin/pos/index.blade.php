@extends("layouts.admin")

@section("styles")
<style>
    .item-card { cursor: pointer; transition: transform 0.2s; border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); background: #fff; }
    .item-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .cart-item { font-size: 0.95rem; border-bottom: 1px dashed #eee!important; }
    .nav-pills .nav-link { border-radius: 10px; font-weight: 600; padding: 10px 20px; color: #64748b; background: #fff; margin-right: 8px; border: 1px solid #e2e8f0; }
    .nav-pills .nav-link.active { background-color: #ff4757; color: white; border-color: #ff4757; box-shadow: 0 4px 12px rgba(255, 71, 87, 0.2); }
    .btn-qty { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
    .cart-container { sticky; top: 100px; height: calc(100vh - 120px); }
    .category-scroll { scrollbar-width: none; -ms-overflow-style: none; }
    .category-scroll::-webkit-scrollbar { display: none; }
</style>
@endsection

@section("content")
<div class="row g-4">
    <!-- Menu Grid -->
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0">Menu Selection</h4>
            <div class="input-group w-50 shadow-sm rounded-pill overflow-hidden border-0">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-0" placeholder="Search menu..." id="menuSearch">
            </div>
        </div>

        <ul class="nav nav-pills mb-4 category-scroll flex-nowrap overflow-auto pb-2" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#cat-all" type="button">All Items</button>
            </li>
            @foreach($items->groupBy("category.name") as $catName => $catItems)
            <li class="nav-item">
                <button class="nav-link text-nowrap" data-bs-toggle="pill" data-bs-target="#cat-{{ Str::slug($catName) }}" type="button">{{ $catName }}</button>
            </li>
            @endforeach
        </ul>
        
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="cat-all" role="tabpanel">
                <div class="row g-3">
                    @foreach($items as $i)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-6 item-element" data-name="{{ strtolower($i->name) }}">
                        <div class="card item-card text-center p-3 h-100 {{ $i->stock_quantity <= 0 ? 'opacity-50' : '' }}" 
                             @if($i->stock_quantity > 0) onclick="openItemModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})" @endif>
                            
                            @if($i->stock_quantity <= 0)
                                <div class="position-absolute top-50 start-50 translate-middle w-100 z-3">
                                    <span class="badge bg-danger rounded-pill px-3 shadow">OUT OF STOCK</span>
                                </div>
                            @endif

                            <div class="bg-light rounded-3 p-3 mb-3">
                                <i class="fas fa-hamburger fa-2x text-warning"></i>
                            </div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $i->name }}</h6>
                            <div class="text-danger fw-bold small mb-2">&#8377;{{ number_format($i->price, 2) }}</div>
                            <div class="progress mb-0" style="height: 4px;">
                                <div class="progress-bar {{ $i->stock_quantity <= $i->low_stock_limit ? 'bg-danger' : 'bg-success' }}" style="width: {{ min(100, ($i->stock_quantity / 50) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            @foreach($items->groupBy("category.name") as $catName => $catItems)
            <div class="tab-pane fade" id="cat-{{ Str::slug($catName) }}" role="tabpanel">
                <div class="row g-3">
                    @foreach($catItems as $i)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-6 item-element" data-name="{{ strtolower($i->name) }}">
                         <div class="card item-card text-center p-3 h-100 {{ $i->stock_quantity <= 0 ? 'opacity-50' : '' }}" 
                             @if($i->stock_quantity > 0) onclick="openItemModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})" @endif>
                            <div class="bg-light rounded-3 p-3 mb-3">
                                <i class="fas fa-utensils fa-2x text-success"></i>
                            </div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $i->name }}</h6>
                            <div class="text-danger fw-bold small">&#8377;{{ number_format($i->price, 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Cart System -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden d-flex flex-column" style="height: calc(100vh - 100px);">
            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-receipt text-muted me-2"></i>Current Order</h5>
                <button class="btn btn-sm btn-outline-danger border-0 rounded-pill" onclick="clearCart()"><i class="fas fa-trash-alt me-1"></i> Clear</button>
            </div>
            
            <div class="p-3 bg-light-subtle border-bottom">
                <div class="btn-group w-100 rounded-3 overflow-hidden shadow-sm" role="group">
                    <input type="radio" class="btn-check" name="order_type" id="type-dinein" value="Dine-in" checked autocomplete="off">
                    <label class="btn btn-outline-primary d-flex align-items-center justify-content-center fw-bold" for="type-dinein" style="height: 42px;"><i class="fas fa-chair me-1"></i>Dine In</label>
                    <input type="radio" class="btn-check" name="order_type" id="type-takeaway" value="Takeaway" autocomplete="off">
                    <label class="btn btn-outline-primary d-flex align-items-center justify-content-center fw-bold" for="type-takeaway" style="height: 42px;"><i class="fas fa-walking me-1"></i>Takeaway</label>
                    <input type="radio" class="btn-check" name="order_type" id="type-delivery" value="Home Delivery" autocomplete="off">
                    <label class="btn btn-outline-primary d-flex align-items-center justify-content-center fw-bold" for="type-delivery" style="height: 42px;"><i class="fas fa-motorcycle me-1"></i>Delivery</label>
                </div>
                <div id="delivery-addr-box" class="mt-2" style="display:none;">
                    <textarea id="delivery_address" class="form-control border-0 bg-white rounded-3" rows="2" placeholder="Enter delivery address..." style="font-size: 0.85rem;"></textarea>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto p-3" id="cartContainer">
                <div id="cartBody">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-basket fa-4x text-light mb-3"></i>
                        <p class="text-muted">No items in the cart yet.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-3 py-3 border-top bg-light-subtle">
                <div class="row g-2 align-items-end">
                    <div class="col-7 position-relative">
                        <label class="text-center small fw-bold text-muted text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Customer (Optional)</label>
                        <div class="input-group shadow-sm border" style="border-radius: 50px; overflow: hidden; background: #fff; height: 42px;">
                            <span class="input-group-text bg-transparent border-0 ps-3 h-100 d-flex align-items-center"><i class="fas fa-search small text-muted"></i></span>
                            <input type="text" id="customer_phone" class="form-control border-0 shadow-none px-2 h-100" placeholder="Search phone..." autocomplete="off" style="font-size: 0.85rem;">
                            <button class="btn btn-outline-secondary border-0 bg-transparent d-none px-3 h-100" type="button" id="clear_cust"><i class="fas fa-times small"></i></button>
                        </div>
                        <div id="customer_dropdown" class="position-absolute w-100 bg-white shadow-lg rounded-3 border d-none" style="z-index: 1060; max-height: 200px; overflow-y: auto; bottom: 100%; left: 0;"></div>
                        <input type="hidden" id="selected_customer_id">
                    </div>
                    <div class="col-5" id="table-number-box">
                        <label class="text-center small fw-bold text-muted text-uppercase mb-1 d-block" style="font-size: 0.65rem;">Table No.</label>
                        <div class="input-group shadow-sm border" style="border-radius: 50px; overflow: hidden; background: #fff; height: 42px;">
                            <span class="input-group-text bg-transparent border-0 ps-3 h-100 d-flex align-items-center"><i class="fas fa-hashtag text-muted small"></i></span>
                            <input type="text" id="table_number" class="form-control border-0 shadow-none ps-1 h-100" placeholder="T-00" style="font-size: 0.85rem;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-3 py-3 border-top bg-white">
                <div class="input-group shadow-sm border p-0 m-0" style="border-radius: 50px; overflow: hidden; background: #fff; display: flex; height: 44px;">
                    <input type="text" id="coupon_code" class="form-control border-0 shadow-none px-3 h-80" placeholder="DISCOUNT COUPON CODE" style="font-weight: 600; font-size: 0.825rem; letter-spacing: 0.5px; color: #475569; border-radius: 0;">
                    <button class="btn text-white border-0 px-3 fw-bold m-0 h-100" type="button" onclick="applyCoupon()" style="background: #1e293b; font-size: 0.8rem; z-index: 2; border-radius: 0; min-width: 90px;">VALIDATE</button>
                </div>
            </div>

            <div class="p-3 order-top bg-white">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold h6 mb-0">&#8377;<span id="subTotal">0.00</span></span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-success">
                    <span>Discount</span>
                    <span class="fw-bold">-&#8377;<span id="discount">0.00</span></span>
                </div>
                <div class="d-flex justify-content-between mb-4 pt-3 border-top">
                    <span class="h5 fw-bold text-dark">Total Payable</span>
                    <span class="h4 fw-bold text-primary mb-0">&#8377;<span id="grandTotal">0.00</span></span>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <select id="payment_method" class="form-select border border-primary-subtle fw-bold shadow-sm" style="height: 40px; border-radius: 50px; font-size: 0.95rem; padding-left: 20px;">
                            <option value="Cash">💵 CASH</option>
                            <option value="Card">💳 CARD</option>
                            <option value="UPI">📱 UPI</option>
                        </select>
                    </div>
                    <div class="col-6">
                         <button class="btn w-100 fw-bold shadow-sm text-white" style="height: 40px; border-radius: 50px; background-color: #ff4757; font-size: 1rem;" onclick="processOrder(event)"><i class="fas fa-check-circle me-2"></i> Place Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Options Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold" id="modalItemName">Customize Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="m_item_id">
                <input type="hidden" id="m_base_price">
                <input type="hidden" id="m_item_name">
                
                <div id="variantsBox" class="mb-4 d-none">
                    <label class="small fw-bold mb-2 text-muted text-uppercase">Select Variation</label>
                    <div id="m_variants_list" class="d-flex flex-column gap-2"></div>
                </div>
                
                <div id="extrasBox" class="mb-0 d-none">
                    <label class="small fw-bold mb-2 text-muted text-uppercase">Add Extras</label>
                    <div id="m_extras_list" class="row g-2"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-between bg-dark text-white border-0 p-3 pt-4">
                <div class="ps-2">
                    <span class="small opacity-75 d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Current Total</span>
                    <h4 class="fw-bold mb-0 text-success">&#8377;<span id="m_total_price">0.00</span></h4>
                </div>
                <button type="button" class="btn btn-primary px-3 py-2 rounded-pill fw-bold" onclick="addConfiguredItem()">ADD ITEM</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section("scripts")
<script>
    let cart = [];
    
    // Load Cart from LocalStorage
    $(document).ready(function() {
        let saved = localStorage.getItem('pos_cart');
        if(saved) {
            cart = JSON.parse(saved);
            renderCart();
        }
    });

    let pos_discount_val = 0;
    
    // Search functionality
    $("#menuSearch").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $(".item-element").filter(function() {
            $(this).toggle($(this).data("name").indexOf(value) > -1)
        });
    });

    $("input[name='order_type']").change(function(){
        let val = this.value;
        if(val === 'Takeaway' || val === 'Home Delivery') $("#table-number-box").slideUp();
        else $("#table-number-box").slideDown();
        if(val === 'Home Delivery') $("#delivery-addr-box").slideDown();
        else $("#delivery-addr-box").slideUp();
    });

    function openItemModal(id, name, price, variants, extras) {
        $("#m_item_id").val(id);
        $("#m_item_name").val(name);
        $("#m_base_price").val(price);
        $("#modalItemName").text(name);
        
        if(variants && variants.length > 0) {
            let varHtml = `<div class="form-check p-0 mb-2">
                <input type="radio" class="btn-check m_variant_sel" name="v_sel" id="v_def" value="" data-price="${price}" data-name="Regular" checked onchange="calcModalPrice()">
                <label class="btn btn-outline-secondary w-100 text-start px-3 py-2 fw-medium" for="v_def">Regular <span class="float-end">&#8377;${price}</span></label>
            </div>`;
            variants.forEach(v => {
                varHtml += `<div class="form-check p-0 mb-2">
                    <input type="radio" class="btn-check m_variant_sel" name="v_sel" id="v_${v.id}" value="${v.id}" data-price="${v.price}" data-name="${v.name}" onchange="calcModalPrice()">
                    <label class="btn btn-outline-secondary w-100 text-start px-3 py-2 fw-medium" for="v_${v.id}">${v.name} <span class="float-end">&#8377;${v.price}</span></label>
                </div>`;
            });
            $("#m_variants_list").html(varHtml);
            $("#variantsBox").removeClass("d-none");
        } else {
            $("#variantsBox").addClass("d-none");
            $("#m_variants_list").html("");
        }
        
        if(extras && extras.length > 0) {
            let extHtml = "";
            extras.forEach(e => {
                extHtml += `<div class="col-6">
                    <input type="checkbox" class="btn-check m_extra_cb" id="ext_${e.id}" value="${e.id}" data-price="${e.price}" data-name="${e.name}" onchange="calcModalPrice()">
                    <label class="btn btn-outline-success btn-sm w-100 text-start p-2 fw-medium" for="ext_${e.id}">${e.name} <br>+&#8377;${e.price}</label>
                </div>`;
            });
            $("#m_extras_list").html(extHtml);
            $("#extrasBox").removeClass("d-none");
        } else {
            $("#extrasBox").addClass("d-none");
        }
        
        calcModalPrice();
        new bootstrap.Modal(document.getElementById("itemModal")).show();
    }
    
    function calcModalPrice() {
        let v_el = $(".m_variant_sel:checked");
        let base = v_el.length ? parseFloat(v_el.data("price")) : parseFloat($("#m_base_price").val());
        let extTotal = 0;
        $(".m_extra_cb:checked").each(function(){
            extTotal += parseFloat($(this).data("price"));
        });
        $("#m_total_price").text((base + extTotal).toFixed(2));
    }
    
    function addConfiguredItem() {
        let id = $("#m_item_id").val();
        let name = $("#m_item_name").val();
        let v_el = $(".m_variant_sel:checked");
        let varId = v_el.val() || null;
        let varName = v_el.data("name") || "";
        let finalPrice = parseFloat($("#m_total_price").text());
        
        let selExtras = [];
        let extNames = [];
        $(".m_extra_cb:checked").each(function(){
            selExtras.push({id: $(this).val(), price: $(this).data("price")});
            extNames.push($(this).data("name"));
        });
        
        cart.push({
            cartId: Date.now(), id: id, name: name,
            variant_id: varId, variant_name: varName,
            price: finalPrice, qty: 1, total: finalPrice,
            extras: selExtras, extras_text: extNames.join(", ")
        });
        
        bootstrap.Modal.getInstance(document.getElementById("itemModal")).hide();
        renderCart();
        showToast('success', 'Added!', name + ' added to cart.');
    }
    
    function updateCartQty(idx, delta) {
        cart[idx].qty += delta;
        if(cart[idx].qty <= 0) cart.splice(idx, 1);
        else cart[idx].total = cart[idx].qty * cart[idx].price;
        renderCart();
    }

    function clearCart() {
        if(cart.length == 0) return;
        Swal.fire({
            title: 'Clear Cart?',
            text: "Are you sure you want to remove all items?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                localStorage.removeItem('pos_cart');
                renderCart();
            }
        });
    }
    
    let pos_coupon_id = null;
    
    // Customer search logic
    $("#customer_phone").on("input", function() {
        let q = $(this).val();
        if(q.length < 1) { $("#customer_dropdown").addClass("d-none"); return; }
        $.get("{{ route('customers.search') }}", { q: q }, function(data) {
            let html = "";
            if(data && Array.isArray(data)) {
                data.forEach(c => {
                    html += `<div class="p-2 border-bottom pointer cust-item" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone}">
                        <div class="fw-bold" style="font-size: 0.8rem;">${c.name}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">${c.phone}</div>
                    </div>`;
                });
            }
            if(html) $("#customer_dropdown").html(html).removeClass("d-none");
            else $("#customer_dropdown").addClass("d-none");
        });
    });

    $(document).on("click", ".cust-item", function() {
        let id = $(this).data("id");
        let phone = $(this).data("phone");
        $("#selected_customer_id").val(id);
        $("#customer_phone").val(phone).prop("readonly", true);
        $("#clear_cust").removeClass("d-none");
        $("#customer_dropdown").addClass("d-none");
    });

    $("#clear_cust").on("click", function() {
        $("#selected_customer_id").val("");
        $("#customer_phone").val("").prop("readonly", false);
        $(this).addClass("d-none");
    });

    // Coupon logic
    window.applyCoupon = function() {
        let code = $("#coupon_code").val();
        if(!code) return showToast('warning', 'Empty!', 'Please enter a coupon code.');
        let subVal = parseFloat($("#subTotal").text()) || 0;
        $.post("{{ route('coupons.check') }}", {code: code, total: subVal, _token: "{{ csrf_token() }}"}, function(res) {
            if(res.status) {
                let cp = res.coupon;
                if(subVal <= 0) return showToast('error', 'Empty Cart', 'Add items before applying coupon!');
                
                pos_discount_val = (subVal * (parseFloat(cp.discount_percentage) || 0)) / 100;
                pos_coupon_id = cp.id;
                
                $("#discount").text(pos_discount_val.toFixed(2));
                renderCart();
                showToast('success', 'Applied!', 'Discount of ' + cp.discount_percentage + '% applied.');
            } else {
                showToast('error', 'Invalid!', res.msg || 'Invalid Coupon');
            }
        }).fail(function() {
            showToast('error', 'Error!', 'Failed to verify coupon. Server error.');
        });
    }

    function renderCart() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
        if(cart.length === 0) {
            $("#cartBody").html(`<div class="text-center py-5"><i class="fas fa-shopping-basket fa-4x text-light mb-3"></i><p class="text-muted">No items in the cart yet.</p></div>`);
            $("#subTotal, #grandTotal").text("0.00");
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        let sub = 0;
        cart.forEach((item, idx) => {
            sub += item.total;
            html += `<div class="list-group-item cart-item bg-transparent px-0 py-2 border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="pe-3">
                        <div class="fw-bold text-dark">${item.name}</div>
                        <div class="small text-muted mb-1">${item.variant_name ? item.variant_name : ''} ${item.extras_text ? '· ' + item.extras_text : ''}</div>
                        <div class="text-primary fw-bold small">&#8377;${item.price.toFixed(2)}</div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center bg-white border rounded-pill shadow-sm mb-1 px-1">
                            <button class="btn btn-sm btn-qty text-danger p-0" onclick="updateCartQty(${idx}, -1)"><i class="fas fa-minus-circle"></i></button>
                            <span class="px-2 fw-bold" style="min-width: 30px;">${item.qty}</span>
                            <button class="btn btn-sm btn-qty text-success p-0" onclick="updateCartQty(${idx}, 1)"><i class="fas fa-plus-circle"></i></button>
                        </div>
                        <div class="fw-bold text-dark">&#8377;${item.total.toFixed(2)}</div>
                    </div>
                </div>
            </div>`;
        });
        html += '</div>';
        $("#cartBody").html(html);
        $("#subTotal").text(sub.toFixed(2));
        $("#grandTotal").text((sub - pos_discount_val).toFixed(2));
    }
    
    function processOrder(e) {
        if(cart.length === 0) return showToast('error', 'Cart Empty', 'Please add some items first!');
        
        let pItems = cart.map(c => ({
            item_id: String(c.id),
            variant_id: c.variant_id ? String(c.variant_id) : null,
            price: parseFloat(c.price),
            quantity: parseInt(c.qty),
            total: parseFloat(c.total),
            extras: c.extras.map(ex => ({id: String(ex.id), price: parseFloat(ex.price)}))
        }));
        
        let btn = $(e.target).closest('button');
        let oldHtml = btn.html();
        btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        let orderData = {
            order_type: $("input[name='order_type']:checked").val(),
            table_number: $("#table_number").val() || "",
            payment_method: $("#payment_method").val(),
            customer_id: $("#selected_customer_id").val() || null,
            customer_phone: $("#customer_phone").val() || null,
            coupon_id: pos_coupon_id,
            items: pItems,
            total_amount: parseFloat($("#subTotal").text()),
            discount_amount: pos_discount_val,
            grand_total: parseFloat($("#grandTotal").text()),
            delivery_address: $("#delivery_address").val() || "",
            _token: "{{ csrf_token() }}"
        };

        $.ajax({
            url: "{{ route('pos.store') }}",
            method: "POST",
            data: orderData,
            success: function(res) {
                if(res.status) {
                    showToast('success', 'Order Placed!', 'Order #'+res.order_number+' has been created.');
                    cart = [];
                    localStorage.removeItem('pos_cart');
                    pos_discount_val = 0;
                    pos_coupon_id = null;
                    $("#discount").text("0.00");
                    $("#coupon_code").val("");
                    $("#clear_cust").click();
                    renderCart();
                    $("#table_number").val("");
                } else {
                    showAlert('error', 'Failed', res.message || 'Something went wrong');
                }
            },
            error: function(xhr) {
                let msg = 'Failed to place order';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showAlert('error', 'Error', msg);
            },
            complete: function() {
                btn.prop("disabled", false).html(oldHtml);
            }
        });
    }
</script>
@endsection

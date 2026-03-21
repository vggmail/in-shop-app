@extends("layouts.admin")

@section("styles")
<style>
    .item-card { cursor: pointer; transition: transform 0.2s; border: none; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .item-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .cart-item { font-size: 0.9rem; }
    .nav-pills .nav-link { border-radius: 20px; font-weight: 500; padding: 8px 20px; color: #495057; }
    .nav-pills .nav-link.active { background-color: #ff4757; color: white; }
    .btn-qty { width: 28px; height: 28px; padding: 0; line-height: 26px; border-radius: 50%; }
</style>
@endsection

@section("content")
<div class="row">
    <!-- Menu Grid -->
    <div class="col-lg-7 col-md-12">
        <ul class="nav nav-pills mb-3 border-bottom pb-2 overflow-auto flex-nowrap" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#cat-all" type="button">All Items</button>
            </li>
            @foreach($items->groupBy("category.name") as $catName => $catItems)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-{{ Str::slug($catName) }}" type="button">{{ $catName }}</button>
            </li>
            @endforeach
        </ul>
        
        <div class="tab-content border-end pe-3" id="pills-tabContent" style="max-height: 75vh; overflow-y: auto;">
            <!-- All Items -->
            <div class="tab-pane fade show active" id="cat-all" role="tabpanel">
                <div class="row g-3">
                    @foreach($items as $i)
                    <div class="col-xl-3 col-lg-4 col-md-4 col-6">
                        <div class="card item-card text-center p-3 h-100 {{ $i->stock_quantity <= 0 ? 'opacity-50 grayscale' : '' }}" 
                             @if($i->stock_quantity > 0) onclick="openItemModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})" @endif>
                            
                            @if($i->stock_quantity <= 0)
                                <div class="position-absolute top-50 start-50 translate-middle w-100">
                                    <span class="badge bg-danger">OUT OF STOCK</span>
                                </div>
                            @endif

                            <i class="fas fa-hamburger fa-2x text-warning mb-2"></i>
                            <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.85rem;">{{ $i->name }}</h6>
                            <span class="text-secondary small font-weight-bold">${{ number_format($i->price, 2) }}</span>
                            <div class="mt-1 small {{ $i->stock_quantity <= $i->low_stock_limit ? 'text-danger fw-bold' : 'text-muted' }}">
                                Stock: {{ $i->stock_quantity }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Category Specific Items -->
            @foreach($items->groupBy("category.name") as $catName => $catItems)
            <div class="tab-pane fade" id="cat-{{ Str::slug($catName) }}" role="tabpanel">
                <div class="row g-3">
                    @foreach($catItems as $i)
                    <div class="col-xl-3 col-lg-4 col-md-4 col-6">
                        <div class="card item-card text-center p-3 h-100 {{ $i->stock_quantity <= 0 ? 'opacity-50 grayscale' : '' }}" 
                             @if($i->stock_quantity > 0) onclick="openItemModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})" @endif>
                            
                            @if($i->stock_quantity <= 0)
                                <div class="position-absolute top-50 start-50 translate-middle w-100">
                                    <span class="badge bg-danger">OUT OF STOCK</span>
                                </div>
                            @endif

                            <i class="fas fa-utensils fa-2x text-success mb-2"></i>
                            <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.85rem;">{{ $i->name }}</h6>
                            <span class="text-secondary small font-weight-bold">${{ number_format($i->price, 2) }}</span>
                            <div class="mt-1 small {{ $i->stock_quantity <= $i->low_stock_limit ? 'text-danger fw-bold' : 'text-muted' }}">
                                Stock: {{ $i->stock_quantity }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Cart System -->
    <div class="col-lg-5 col-md-12 mt-4 mt-lg-0">
        <div class="card border-0 shadow-lg rounded-3" style="min-height: 80vh;">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h5 class="fw-bold mb-0">Current Order</h5>
            </div>
            
            <div class="card-body d-flex flex-column p-0">
                <!-- Order Type Switches -->
                <div class="d-flex p-3 bg-light border-bottom">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="order_type" id="type-dinein" value="Dine-in" checked autocomplete="off">
                        <label class="btn btn-outline-danger" for="type-dinein"><i class="fas fa-chair"></i> Dine In</label>
                        <input type="radio" class="btn-check" name="order_type" id="type-takeaway" value="Takeaway" autocomplete="off">
                        <label class="btn btn-outline-danger" for="type-takeaway"><i class="fas fa-shopping-bag"></i> Takeaway</label>
                    </div>
                </div>
                
                <div class="p-3 bg-light border-bottom" id="table-number-box">
                    <input type="text" id="table_number" class="form-control form-control-sm" placeholder="Table Number (e.g. T-04)">
                </div>
            
                <div class="table-responsive p-2 flex-grow-1" style="max-height: 250px; overflow-y:auto; background: #fff;">
                    <table class="table table-sm table-hover mb-0" id="cartTable">
                        <tbody id="cartBody">
                            <tr class="text-center text-muted"><td class="py-4">Cart is empty</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 bg-light border-top mt-auto">
                    <!-- Discount -->
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" id="couponCode" class="form-control" placeholder="Discount Coupon Code">
                        <button class="btn btn-dark" onclick="applyCoupon()">Validate</button>
                    </div>
                    <div id="couponMsg" class="mb-2 font-weight-bold" style="font-size: 13px;"></div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary fw-bold">Subtotal</span>
                        <span class="fw-bold">$<span id="subTotal">0.00</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span class="fw-bold">Discount</span>
                        <span class="fw-bold">-$<span id="discount">0.00</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-top pt-2 fs-4">
                        <span class="fw-bold text-dark">Total</span>
                        <span class="fw-bold text-success">$<span id="grandTotal">0.00</span></span>
                    </div>
                    
                    <div class="mb-3 bg-white p-2 border rounded">
                        <label class="small fw-bold text-muted mb-1 text-uppercase">Customer (Optional)</label>
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-user"></i></span>
                            <input type="text" id="customer_search" class="form-control border-start-0 ps-0" placeholder="Search or add by Name/Phone...">
                            <input type="hidden" id="customer_id">
                        </div>
                        <div id="customer_info_display" class="d-none">
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded border">
                                <div><i class="fas fa-check-circle text-success me-1"></i> <span id="sel_customer_name" class="fw-bold"></span></div>
                                <button class="btn btn-sm text-danger p-0" onclick="clearCustomer()"><i class="fas fa-times-circle"></i></button>
                            </div>
                        </div>
                        <div id="customer_add_display" class="d-none mt-2 p-2 border rounded border-primary bg-light">
                             <div class="small fw-bold text-primary mb-1">REGISTER NEW CUSTOMER?</div>
                             <input type="text" id="new_cust_phone" class="form-control form-control-sm mb-1" placeholder="Mobile Number">
                             <button class="btn btn-primary btn-sm w-100" onclick="confirmNewCust()">Apply as New Customer</button>
                        </div>
                    </div>
                    
                    <textarea id="order_note" class="form-control mb-3" rows="2" placeholder="Kitchen Note (e.g. Less Spicy, No Onions)..."></textarea>
                    
                    <div class="d-flex gap-2">
                        <select id="payment_method" class="form-select form-select-lg shadow-sm border-0 font-weight-bold text-center">
                            <option value="Cash">💵 CASH</option>
                            <option value="Card">💳 CARD</option>
                            <option value="UPI">📱 UPI</option>
                        </select>
                        <button class="btn btn-success btn-lg w-50 fw-bold shadow-sm" onclick="processOrder()"><i class="fas fa-print"></i> PLACE ORDER</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Options Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalItemName">Item Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="m_item_id">
                <input type="hidden" id="m_base_price">
                <input type="hidden" id="m_item_name">
                
                <h6 class="text-muted mb-3">Base Price: $<span id="m_display_price"></span></h6>
                
                <div id="variantsBox" class="mb-3 d-none">
                    <h6 class="fw-bold fs-6">Choose Size/Variant</h6>
                    <select id="m_variant" class="form-select" onchange="calcModalPrice()">
                        <!-- filled dynamically -->
                    </select>
                </div>
                
                <div id="extrasBox" class="mb-3 d-none">
                    <h6 class="fw-bold fs-6">Extra Toppings</h6>
                    <div id="m_extras_list" class="d-flex flex-wrap gap-2">
                        <!-- filled dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between border-top-0 bg-light">
                <h5 class="fw-bold text-danger mb-0">Total: $<span id="m_total_price">0.00</span></h5>
                <button type="button" class="btn btn-primary px-4 rounded-pill" onclick="addConfiguredItem()"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section("scripts")
<script>
    let cart = [];
    let discountVal = 0;
    
    // Switch table number visibility
    $("input[name='order_type']").change(function(){
        if(this.value === "Takeaway") $("#table-number-box").slideUp();
        else $("#table-number-box").slideDown();
    });

    // Item Configurator
    let currentExtras = [];
    let currentVariants = [];
    
    function openItemModal(id, name, price, variants, extras) {
        $("#m_item_id").val(id);
        $("#m_item_name").val(name);
        $("#m_base_price").val(price);
        $("#modalItemName").text(name);
        $("#m_display_price").text(parseFloat(price).toFixed(2));
        
        currentVariants = variants;
        currentExtras = extras;
        
        if(variants.length > 0) {
            let options = `<option value="" data-price="${price}">Standard Size (Default)</option>`;
            variants.forEach(v => {
                options += `<option value="${v.id}" data-price="${v.price}">${v.name} (+$${v.price})</option>`;
            });
            $("#m_variant").html(options);
            $("#variantsBox").removeClass("d-none");
        } else {
            $("#variantsBox").addClass("d-none");
            $("#m_variant").html('<option value="" data-price="'+price+'"></option>');
        }
        
        if(extras.length > 0) {
            let extHtml = "";
            extras.forEach(e => {
                extHtml += `<div class="form-check border rounded p-2 ps-4 mb-0 bg-white" style="flex: 1 1 45%;">
                                <input class="form-check-input m_extra_cb" type="checkbox" value="${e.id}" data-price="${e.price}" data-name="${e.name}" id="ext_${e.id}" onchange="calcModalPrice()">
                                <label class="form-check-label w-100" style="cursor:pointer;" for="ext_${e.id}">${e.name} <span class="text-success float-end">+$${e.price}</span></label>
                            </div>`;
            });
            $("#m_extras_list").html(extHtml);
            $("#extrasBox").removeClass("d-none");
        } else {
            $("#extrasBox").addClass("d-none");
            $("#m_extras_list").html("");
        }
        
        calcModalPrice();
        var myModal = new bootstrap.Modal(document.getElementById("itemModal"));
        myModal.show();
    }
    
    function calcModalPrice() {
        let base = parseFloat($("#m_variant option:selected").data("price") || $("#m_base_price").val());
        let extTotal = 0;
        $(".m_extra_cb:checked").each(function(){
            extTotal += parseFloat($(this).data("price"));
        });
        $("#m_total_price").text((base + extTotal).toFixed(2));
    }
    
    function addConfiguredItem() {
        let id = $("#m_item_id").val();
        let name = $("#m_item_name").val();
        let varId = $("#m_variant").val();
        let varName = varId ? $("#m_variant option:selected").text().split(" ")[0] : "";
        let finalPrice = parseFloat($("#m_total_price").text());
        
        let selExtras = [];
        let extNames = [];
        $(".m_extra_cb:checked").each(function(){
            selExtras.push({id: $(this).val(), price: $(this).data("price")});
            extNames.push($(this).data("name"));
        });
        
        // Check if identical configuration exists in cart
        let existingIdx = cart.findIndex(c => c.id == id && c.variant_id == varId && JSON.stringify(c.extras) == JSON.stringify(selExtras));
        
        if(existingIdx !== -1) {
            cart[existingIdx].qty++;
            cart[existingIdx].total = cart[existingIdx].qty * cart[existingIdx].price;
        } else {
            cart.push({
                cartId: Date.now(),
                id: id,
                name: name,
                variant_id: varId,
                variant_name: varName,
                price: finalPrice,
                qty: 1,
                total: finalPrice,
                extras: selExtras,
                extras_text: extNames.join(", ")
            });
        }
        
        bootstrap.Modal.getInstance(document.getElementById("itemModal")).hide();
        renderCart();
    }
    
    function updateCartQty(idx, action) {
        if(action === 1) cart[idx].qty++;
        else if(action === -1) cart[idx].qty--;
        
        if(cart[idx].qty <= 0) cart.splice(idx, 1);
        else cart[idx].total = cart[idx].qty * cart[idx].price;
        renderCart();
    }
    
    function renderCart() {
        if(cart.length === 0) {
            $("#cartBody").html(`<tr class="text-center text-muted"><td class="py-4 border-0">Cart is empty</td></tr>`);
            $("#subTotal, #grandTotal").text("0.00");
            return;
        }
        
        let html = ""; let sub = 0;
        cart.forEach((item, idx) => {
            sub += item.total;
            
            let details = "";
            if(item.variant_name) details += `<span class="badge bg-secondary me-1">${item.variant_name}</span>`;
            if(item.extras_text) details += `<small class="text-muted d-block mt-1" style="font-size:10px;"><i class="fas fa-plus"></i> ${item.extras_text}</small>`;
            
            html += `<tr class="border-bottom cart-item">
                <td class="pt-2 w-50" style="white-space:normal; line-height:1.2;">
                    <strong class="d-block mb-1">${item.name}</strong>
                    ${details}
                </td>
                <td class="text-center pt-2 align-middle">
                    <div class="d-flex align-items-center justify-content-center bg-white border rounded">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 text-danger fw-bold" onclick="updateCartQty(${idx}, -1)">-</button>
                        <span class="px-2 font-weight-bold">${item.qty}</span>
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 text-success fw-bold" onclick="updateCartQty(${idx}, 1)">+</button>
                    </div>
                </td>
                <td class="pt-3 fw-bold text-end align-middle bg-light text-dark">$${item.total.toFixed(2)}</td>
            </tr>`;
        });
        $("#cartBody").html(html);
        $("#subTotal").text(sub.toFixed(2));
        calcTotals(sub);
    }
    
    function applyCoupon() {
        let code = $("#couponCode").val();
        if(!code) return;
        $.post("{{ route('coupons.check') }}", {_token: "{{ csrf_token() }}", code: code}, function(res) {
            if(res.status) {
                let sub = parseFloat($("#subTotal").text());
                let c = res.coupon;
                if(c.min_bill_amount > 0 && sub < c.min_bill_amount) {
                    $("#couponMsg").html(`<span class="text-danger">Minimum amount: $${c.min_bill_amount} required</span>`);
                    return;
                }
                discountVal = sub * (parseFloat(c.discount_percentage) / 100);
                $("#couponMsg").html(`<span class="text-success"><i class="fas fa-check-circle"></i> Applied ${c.discount_percentage}% OFF (-$${discountVal.toFixed(2)})</span>`);
                calcTotals(sub);
            } else {
                $("#couponMsg").html(`<span class="text-danger"><i class="fas fa-times-circle"></i> Invalid Code</span>`);
            }
        });
    }
    
    function calcTotals(sub) {
        $("#discount").text(discountVal.toFixed(2));
        let gt = sub - discountVal;
        $("#grandTotal").text(gt > 0 ? gt.toFixed(2) : "0.00");
    }
    
    // Customer Add / Search
    let allCustomers = @json($customers);
    $("#customer_search").on("input", function(){
        let query = $(this).val().trim();
        if(query.length < 2) { $("#customer_add_display").addClass("d-none"); return; }
        
        let found = allCustomers.find(c => c.phone == query || c.name.toLowerCase().includes(query.toLowerCase()));
        if(found) {
            selectCustomer(found.id, found.name);
            $("#customer_add_display").addClass("d-none");
        } else {
            $("#customer_add_display").removeClass("d-none");
            $("#new_cust_phone").val(query);
        }
    });

    function selectCustomer(id, name) {
        $("#customer_id").val(id);
        $("#sel_customer_name").text(name);
        $("#customer_info_display").removeClass("d-none");
        $("#customer_search").val("").addClass("d-none");
        $("#customer_add_display").addClass("d-none");
    }

    function clearCustomer() {
        $("#customer_id").val("");
        $("#customer_search").removeClass("d-none");
        $("#customer_info_display").addClass("d-none");
    }

    function confirmNewCust() {
        let name = $("#customer_search").val();
        let phone = $("#new_cust_phone").val();
        selectCustomer("new", name + " (" + phone + ") ");
        window.new_cust_name = name;
        window.new_cust_phone = phone;
    }

    function processOrder() {
        if(cart.length === 0) return alert("Cart is empty!");
        
        let pItems = cart.map(c => {
            return {
                item_id: c.id,
                variant_id: c.variant_id || null,
                price: c.price,
                quantity: c.qty,
                total: c.total,
                extras: c.extras // array of {id, price}
            };
        });
        
        let btn = $(event.target);
        btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        let data = {
            _token: "{{ csrf_token() }}",
            order_type: $("input[name='order_type']:checked").val(),
            table_number: $("#table_number").val(),
            customer_id: $("#customer_id").val() === "new" ? null : $("#customer_id").val(),
            customer_name: (window.new_cust_name || null),
            customer_phone: (window.new_cust_phone || null),
            note: $("#order_note").val(),
            payment_method: $("#payment_method").val(),
            items: pItems,
            total_amount: parseFloat($("#subTotal").text()),
            discount_amount: discountVal,
            grand_total: parseFloat($("#grandTotal").text())
        };
        
        $.post("{{ route('pos.store') }}", data, function(res) {
            if(res.status) {
                if(confirm("Order #" + res.order_id + " generated!\nPrint Receipt now?")) {
                    window.open("/orders/" + res.order_id + "/invoice", "_blank", "width=400,height=600");
                }
                location.reload();
            } else {
                alert(res.msg);
                btn.prop("disabled", false).html('<i class="fas fa-print"></i> PLACE ORDER');
            }
        });
    }
</script>
@endsection
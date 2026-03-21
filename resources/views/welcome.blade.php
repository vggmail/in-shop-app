<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fast Food Hub - Fresh Burgers, Pizza & More | Order Online</title>
    <meta name="description" content="Craving something delicious? Fast Food Hub offers the freshest burgers, cheesy pizzas, and tasty sides. Order online for quick delivery or takeaway.">
    <meta name="keywords" content="fast food, online ordering, burger, pizza, takeaway, delivery, restaurant">
    <meta name="author" content="Fast Food Hub">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Fast Food Hub - Delicious Food Delivered Fast">
    <meta property="og:description" content="Get the best burgers and pizzas in town. Fast, fresh, and friendly service. Order now!">
    <meta property="og:image" content="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Fast Food Hub - Delicious Food Delivered Fast">
    <meta property="twitter:description" content="Get the best burgers and pizzas in town. Fast, fresh, and friendly service. Order now!">
    <meta property="twitter:image" content="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { font-family: "Outfit", sans-serif; background-color: #f8f9fa; color: var(--dark); overflow-x: hidden; padding-bottom: 100px; }
        .hero { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url("https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800"); background-size: cover; height: 30vh; border-radius: 0 0 30px 30px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; }
        .category-pill { background: white; border: 1px solid #eee; padding: 8px 20px; border-radius: 30px; white-space: nowrap; cursor: pointer; transition: 0.3s; font-weight: 600; color: #777; }
        .category-pill.active { background: var(--primary); color: white; border-color: var(--primary); }
        .food-card { border: none; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; height: 100%; transition: 0.3s; cursor: pointer; }
        .food-card:active { transform: scale(0.95); }
        .food-img { height: 140px; background: #eee; display: flex; align-items: center; justify-content: center; color: #ccc; }
        .cart-float { position: fixed; bottom: 20px; right: 20px; left: 20px; background: var(--dark); color: white; padding: 15px 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; cursor: pointer; display: none; }
        .modal-content { border-radius: 25px; border: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .coupon-pill { background: #fff5f5; border: 1px dashed var(--primary); padding: 5px 15px; border-radius: 10px; min-width: 150px; }
    </style>
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Restaurant",
      "name": "Fast Food Hub",
      "image": "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800",
      "url": "{{ url('/') }}",
      "telephone": "",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Main Street",
        "addressLocality": "Your City",
        "postalCode": "000000",
        "addressCountry": "IN"
      },
      "menu": "{{ url('/') }}",
      "servesCuisine": "Fast Food, Burger, Pizza",
      "priceRange": "₹₹"
    }
    </script>
</head>
<body>

    <div class="hero">
        <div><h1 class="fw-bold">FAST FOOD HUB</h1><p class="small opacity-75">Order your favorite meals instantly</p></div>
    </div>

    <div class="container mt-4">
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
            <div class="col-6 col-md-4 food-item-box" data-cat="{{ Str::slug($i->category->name) }}">
                <div class="card food-card" onclick="openFoodModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})">
                    
                    <div class="food-img position-relative">
                        <i class="fas fa-hamburger fa-2x"></i>
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-success rounded-pill shadow-sm" style="font-size: 8px;">HOT & FRESH</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1">{{ $i->name }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-primary fw-bold">₹{{ number_format($i->price, 2) }}</span>
                                @if($i->mrp && $i->mrp > $i->price)
                                    <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹{{ number_format($i->mrp, 2) }}</span>
                                @endif
                            </div>
                            @if($i->mrp && $i->mrp > $i->price)
                                <span class="badge bg-soft-danger text-danger border-0 small" style="font-size: 8px;">{{ round((($i->mrp - $i->price) / $i->mrp) * 100) }}% OFF</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="cart-float" id="cart-float" data-bs-toggle="modal" data-bs-target="#checkoutModal">
        <span class="fw-bold"><i class="fas fa-shopping-cart me-2"></i> <span id="cart-count">0</span> Items</span>
        <span class="fw-bold">Pay: ₹<span id="cart-total">0.00</span></span>
    </div>

    <!-- Food Modal -->
    <div class="modal fade" id="foodModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
        <h4 class="fw-bold mb-1" id="m-food-name">Food Name</h4>
        <p class="text-muted small mb-4" id="m-food-desc">Includes fresh ingredients & special sauces.</p>

        <div id="m-variants-box" class="mb-4 d-none">
            <label class="fw-bold small text-uppercase text-muted mb-2">Select Variant</label>
            <div id="m-variants-list" class="d-grid gap-2"></div>
        </div>
        <div id="m-extras-box" class="mb-4 d-none">
            <label class="fw-bold small text-uppercase text-muted mb-2">Extra Toppings</label>
            <div id="m-extras-list" class="row g-2"></div>
        </div>

        <div class="d-grid mt-4">
            <button class="btn btn-danger btn-lg rounded-pill fw-bold py-3" onclick="addToCart()">ADD TO CART - ₹<span id="m-total">0.00</span></button>
        </div>
    </div></div></div></div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-body p-4">
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
        <h4 class="fw-bold mb-4"><i class="fas fa-shopping-basket text-danger me-2"></i> Finalize Order</h4>
        
        <div id="checkout-list" class="mb-4 bg-light p-3 rounded-4" style="max-height: 250px; overflow-y: auto;"></div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="small fw-bold text-muted mb-1">YOUR NAME</label>
                <input type="text" id="cust_name" class="form-control form-control-lg border-0 bg-light rounded-3" placeholder="Enter Full Name">
                <div class="invalid-feedback">Please enter your correct name.</div>
            </div>
            <div class="col-12">
                <label class="small fw-bold text-muted mb-1">MOBILE NUMBER (10 DIGITS)</label>
                <input type="tel" id="cust_phone" maxlength="10" class="form-control form-control-lg border-0 bg-light rounded-3" placeholder="e.g. 9876543210">
                <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
            </div>
        </div>

        <div class="mb-4">
            <label class="small fw-bold text-muted mb-2 text-uppercase">Where will you eat?</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="order_type" id="type-dinein" value="Dine-in" checked>
                <label class="btn btn-outline-danger py-2" for="type-dinein"><i class="fas fa-chair me-1"></i> DINE-IN</label>
                <input type="radio" class="btn-check" name="order_type" id="type-takeaway" value="Takeaway">
                <label class="btn btn-outline-danger py-2" for="type-takeaway"><i class="fas fa-shopping-bag me-1"></i> TAKEAWAY</label>
            </div>
        </div>

        <div class="mb-4" id="table-number-box">
            <input type="text" id="table_number" class="form-control form-control-lg border-0 bg-light rounded-3" placeholder="Enter Table Number (e.g. T-04)">
        </div>

        <div class="p-3 bg-light rounded-4 mb-4">
            <label class="small fw-bold text-muted mb-2 text-uppercase d-block">Apply Coupon</label>
            <div class="input-group">
                <input type="text" id="coupon_code" class="form-control border-0 bg-white shadow-none" placeholder="Enter coupon code">
                <button class="btn btn-primary px-4 fw-bold" type="button" onclick="applyCoupon()">APPLY</button>
            </div>
            <div id="coupon-msg" class="small mt-1 px-2 d-none"></div>
        </div>

        <div class="px-2">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Subtotal</span>
                <span class="fw-bold text-dark">₹<span id="checkout-subtotal">0.00</span></span>
            </div>
            <div class="d-flex justify-content-between mb-1 d-none" id="discount-row">
                <span class="text-danger fw-bold">Discount (<span id="discount-percent">0</span>%)</span>
                <span class="fw-bold text-danger">-₹<span id="discount-amount">0.00</span></span>
            </div>
            <div class="d-flex justify-content-between mb-4 mt-2 border-top pt-2">
                <h5 class="fw-bold">Grand Total</h5>
                <h5 class="fw-bold text-success">₹<span id="checkout-total">0.00</span></h5>
            </div>
        </div>

        <button class="btn btn-dark btn-lg w-100 rounded-pill py-3 fw-bold" id="placeOrderBtn" onclick="submitOrder()">PLACE ORDER NOW</button>
    </div></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        let currentItem = null;

        $(document).ready(function() {
            renderCart();
        });

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        function filterCat(slug) {
            $(".category-pill").removeClass("active");
            $(event.target).addClass("active");
            if(slug==='all') $(".food-item-box").show();
            else { $(".food-item-box").hide(); $(`.food-item-box[data-cat="${slug}"]`).show(); }
        }

        function openFoodModal(id, name, price, variants, extras) {
            currentItem = { id, name, price, variants, extras };
            $("#m-food-name").text(name);
            $("#m-variants-list, #m-extras-list").empty();
            if(variants.length) {
                $("#m-variants-box").removeClass("d-none");
                // Always add a Standard/Default choice if not specifically forced
                $("#m-variants-list").append(`<input type="radio" class="btn-check" name="food_var" id="var_default" value="" data-price="0" data-name="Standard" checked>
                    <label class="btn btn-outline-dark rounded-pill py-2 text-start px-3" for="var_default">Standard <span class="float-end">Included</span></label>`);
                
                variants.forEach(v => {
                    $("#m-variants-list").append(`<input type="radio" class="btn-check" name="food_var" id="var_${v.id}" value="${v.id}" data-price="${v.price}" data-name="${v.name}">
                        <label class="btn btn-outline-dark rounded-pill py-2 text-start px-3" for="var_${v.id}">${v.name} <span class="float-end">+₹${v.price}</span></label>`);
                });
            } else { $("#m-variants-box").addClass("d-none"); }

            if(extras.length) {
                $("#m-extras-box").removeClass("d-none");
                extras.forEach(e => {
                    $("#m-extras-list").append(`<div class="col-6"><input type="checkbox" class="btn-check m-extra-cb" id="ext_${e.id}" value="${e.id}" data-price="${e.price}" data-name="${e.name}">
                        <label class="btn btn-outline-secondary rounded-pill w-100 text-start px-3 py-2" for="ext_${e.id}">${e.name} <br><span class="small">+₹${e.price}</span></label></div>`);
                });
            } else { $("#m-extras-box").addClass("d-none"); }

            $(document).on("change", ".btn-check", updateMTotal);
            updateMTotal();
            new bootstrap.Modal(document.getElementById("foodModal")).show();
        }

        function updateMTotal() {
            let total = currentItem.price;
            let vPrice = parseFloat($("input[name='food_var']:checked").data("price") || 0);
            let ePrice = 0;
            $(".m-extra-cb:checked").each(function(){ ePrice += parseFloat($(this).data("price")); });
            $("#m-total").text((total + vPrice + ePrice).toFixed(2));
        }

        function addToCart() {
            let varInput = $("input[name='food_var']:checked");
            let extras = [];
            $(".m-extra-cb:checked").each(function(){
                extras.push({ id: $(this).val(), price: parseFloat($(this).data("price")), name: $(this).data("name") });
            });
            
            cart.push({
                item_id: currentItem.id,
                name: currentItem.name,
                price: parseFloat($("#m-total").text()),
                variant_id: varInput.val() || null,
                variant_name: varInput.data("name") || "",
                extras: extras,
                qty: 1
            });
            bootstrap.Modal.getInstance(document.getElementById("foodModal")).hide();
            saveCart();
            renderCart();
        }

        function renderCart() {
            if(!cart.length) { $("#cart-float").hide(); return; }
            $("#cart-float").css("display", "flex");
            let count = cart.length;
            let subtotal = cart.reduce((s, o) => s + (o.price * o.qty), 0);
            $("#cart-count").text(count);
            $("#cart-total, #checkout-subtotal").text(subtotal.toFixed(2));
            
            let html = "";
            cart.forEach((c, i) => {
                html += `<div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">${c.name}</h6>
                        <small class="text-muted">${c.variant_name ? c.variant_name+', ' : ''} ${c.extras.length ? c.extras.map(e=>e.name).join(', ') : 'No Extras'}</small>
                        <div class="mt-1"><button class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${i})"><i class="fas fa-trash-alt me-1"></i> Remove</button></div>
                    </div>
                    <div class="text-end fw-bold text-danger">₹${c.price.toFixed(2)}</div>
                </div>`;
            });
            $("#checkout-list").html(html);
            updateFinalTotal();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            saveCart();
            renderCart();
        }

        let appliedCoupon = null;

        function applyCoupon() {
            let code = $("#coupon_code").val().trim();
            if(!code) return;
            let subtotal = parseFloat($("#checkout-subtotal").text());
            
            $.post("{{ route('coupons.check') }}", {
                _token: "{{ csrf_token() }}",
                code: code
            }, function(res) {
                let msg = $("#coupon-msg");
                msg.removeClass("d-none text-success text-danger");
                if(res.status) {
                    if(subtotal < res.coupon.min_bill_amount) {
                        msg.addClass("text-danger").text(`Min. order of ₹${res.coupon.min_bill_amount} required!`);
                    } else {
                        appliedCoupon = res.coupon;
                        msg.addClass("text-success").text(`Success! ${res.coupon.discount_percentage}% discount applied.`);
                        updateFinalTotal();
                    }
                } else {
                    appliedCoupon = null;
                    msg.addClass("text-danger").text("Invalid or expired coupon code.");
                    updateFinalTotal();
                }
            });
        }

        function updateFinalTotal() {
            let subtotal = parseFloat($("#checkout-subtotal").text());
            let discount = 0;
            if(appliedCoupon) {
                discount = (subtotal * appliedCoupon.discount_percentage) / 100;
                $("#discount-row").removeClass("d-none");
                $("#discount-percent").text(appliedCoupon.discount_percentage);
                $("#discount-amount").text(discount.toFixed(2));
            } else {
                $("#discount-row").addClass("d-none");
            }
            $("#checkout-total").text((subtotal - discount).toFixed(2));
        }

        function copyCoupon(code) {
            $("#coupon_code").val(code);
            $("#checkoutModal").modal("show");
            setTimeout(applyCoupon, 500);
        }

        // Toggle Table Number visibility
        $(document).on("change", "input[name='order_type']", function(){
            if($(this).val() === "Dine-in") $("#table-number-box").removeClass("d-none");
            else $("#table-number-box").addClass("d-none");
        });

        function submitOrder() {
            let name = $("#cust_name").val().trim();
            let phone = $("#cust_phone").val().trim();
            let isValid = true;

            // Reset Errors
            $("#cust_name, #cust_phone").removeClass("is-invalid");

            if(!name || name.length < 3) {
                $("#cust_name").addClass("is-invalid");
                isValid = false;
            }

            if(!phone || !/^\d{10}$/.test(phone)) {
                $("#cust_phone").addClass("is-invalid");
                isValid = false;
            }

            if(!isValid) return;

            let items = cart.map(c => ({
                item_id: c.item_id,
                variant_id: c.variant_id,
                price: c.price,
                quantity: c.qty,
                total: c.price * c.qty,
                extras: c.extras.map(e => ({ id: e.id, price: e.price }))
            }));

            $("#placeOrderBtn").prop("disabled", true).text("Processing...");

            $.post("{{ route('home.store') }}", {
                _token: "{{ csrf_token() }}",
                customer_name: name,
                customer_phone: phone,
                order_type: $("input[name='order_type']:checked").val(),
                table_number: $("#table_number").val(),
                items: JSON.stringify(items),
                total_amount: parseFloat($("#checkout-subtotal").text()),
                discount_amount: parseFloat($("#discount-amount").text() || 0),
                grand_total: parseFloat($("#checkout-total").text())
            }, function(res) {
                if(res.status) {
                    alert(res.msg);
                    localStorage.removeItem('cart');
                    location.reload();
                } else {
                    alert("Error: " + res.msg);
                    $("#placeOrderBtn").prop("disabled", false).text("PLACE ORDER NOW");
                }
            });
        }
    </script>
</body>
</html>
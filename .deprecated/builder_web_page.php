<?php
$dirV = __DIR__ . '/resources/views';

$content = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fast Food Hub - Home Delivery & Takeaway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { font-family: "Outfit", sans-serif; background-color: #fff; color: var(--dark); overflow-x: hidden; }
        
        /* Hero Section */
        .hero { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url("https://images.unsplash.com/photo-1513104890138-7c749659a591?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxfDB8MXxhbGx8fHx8fHx8fHwxNjE4OTc4OTU0&ixlib=rb-1.2.1&q=80&w=1080"); 
                background-size: cover; background-position: center; height: 40vh; display: flex; align-items: center; justify-content: center; text-align: center; color: white; margin-bottom: -30px; border-radius: 0 0 40px 40px; }
        .hero h1 { font-weight: 800; font-size: 2.5rem; text-shadow: 2px 2px 10px rgba(0,0,0,0.3); }

        /* Menu Section */
        .category-pill { background: white; border: 1px solid #eee; padding: 10px 25px; border-radius: 30px; white-space: nowrap; cursor: pointer; transition: 0.3s; font-weight: 600; color: #777; }
        .category-pill.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3); }
        
        .food-card { border: none; border-radius: 25px; transition: 0.3s; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; }
        .food-card:active { transform: scale(0.95); }
        .food-img { width: 100%; height: 160px; object-fit: cover; background: #f9f9f9; display: flex; align-items: center; justify-content: center; color: #ddd; }
        .food-card .card-body { padding: 15px; }
        .food-card .price { font-weight: 800; color: var(--primary); font-size: 1.2rem; }
        .add-btn { background: var(--primary); color: white; border-radius: 15px; border: none; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        /* Floating Cart */
        .cart-float { position: fixed; bottom: 20px; right: 20px; left: 20px; background: var(--dark); color: white; padding: 15px 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 15px 35px rgba(0,0,0,0.3); z-index: 1000; display: none; text-decoration: none; }
        .cart-float:hover { color: white; }

        /* Bottom Nav (Mobile) */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #eee; display: flex; justify-content: space-around; padding: 10px 0; z-index: 999; }
        .nav-item-m { text-align: center; color: #aaa; text-decoration: none; font-size: 0.8rem; }
        .nav-item-m.active { color: var(--primary); }
        .nav-item-m i { font-size: 1.5rem; display: block; margin-bottom: 2px; }

        .modal-content { border-radius: 30px; border: none; }
        .modal-body { padding: 30px; }
    </style>
</head>
<body>

    <!-- Hero -->
    <div class="hero">
        <div>
            <h1>Fast Food Hub</h1>
            <p class="opacity-75">Deliciousness delivered to your door</p>
        </div>
    </div>

    <div class="container mt-5 pt-4 pb-5 mb-5">
        <!-- Categories -->
        <div class="d-flex overflow-auto gap-2 pb-3 no-scrollbar" style="-ms-overflow-style: none; scrollbar-width: none;">
            <div class="category-pill active" onclick="filterCat(\'all\')">All Menu</div>
            @foreach($categories as $c)
                <div class="category-pill" onclick="filterCat(\'{{ Str::slug($c->name) }}\')">{{ $c->name }}</div>
            @endforeach
        </div>

        <!-- Food Grid -->
        <div class="row g-3 mt-2" id="menu-grid">
            @foreach($items as $i)
            <div class="col-6 col-md-4 col-lg-3 food-item-box" data-cat="{{ Str::slug($i->category->name) }}">
                <div class="card food-card" onclick="openFoodModal({{ $i->id }}, \'{{ addslashes($i->name) }}\', {{ $i->price }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})">
                    <div class="food-img">
                        <i class="fas fa-hamburger fa-3x"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">{{ $i->name }}</h6>
                        <small class="text-muted d-block mb-2">{{ $i->category->name }}</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price">${{ number_format($i->price, 2) }}</span>
                            <button class="add-btn"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Floating Checkout -->
    <a href="#" class="cart-float" id="cart-float" onclick="goToCheckout()">
        <span class="fw-bold"><i class="fas fa-shopping-basket me-2"></i> <span id="cart-count">0</span> Items</span>
        <span class="fw-bold">View Cart: $<span id="cart-total">0.00</span> <i class="fas fa-arrow-right ms-2"></i></span>
    </a>

    <!-- Mobile Navigation -->
    <div class="bottom-nav d-md-none">
        <a href="#" class="nav-item-m active"><i class="fas fa-home"></i>Home</a>
        <a href="#" class="nav-item-m"><i class="fas fa-search"></i>Search</a>
        <a href="#" class="nav-item-m"><i class="fas fa-heart"></i>Favs</a>
        <a href="#" class="nav-item-m"><i class="fas fa-user"></i>Account</a>
    </div>

    <!-- Configuration Modal -->
    <div class="modal fade" id="foodModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
                    <h3 class="fw-bold mt-4 mb-2" id="m-food-name">Food Name</h3>
                    <p class="text-muted" id="m-food-price">$0.00</p>
                    
                    <div id="m-variants-box" class="mt-4 d-none">
                        <label class="fw-bold mb-2">Select Size</label>
                        <div id="m-variants-list" class="d-grid gap-2"></div>
                    </div>

                    <div id="m-extras-box" class="mt-4 d-none">
                        <label class="fw-bold mb-2">Add Extras</label>
                        <div id="m-extras-list" class="row g-2"></div>
                    </div>

                    <div class="mt-5 d-grid">
                        <button class="btn btn-danger btn-lg rounded-pill py-3 fw-bold" onclick="addToCart()">ADD TO CART - $<span id="m-total">0.00</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        let currentItem = null;

        function filterCat(slug) {
            $(".category-pill").removeClass("active");
            $(event.target).addClass("active");
            if(slug === "all") $(".food-item-box").fadeIn();
            else {
                $(".food-item-box").hide();
                $(`.food-item-box[data-cat="${slug}"]`).fadeIn();
            }
        }

        function openFoodModal(id, name, price, variants, extras) {
            currentItem = { id, name, price, variants, extras, selVariant: null, selExtras: [] };
            $("#m-food-name").text(name);
            $("#m-food-price").text("$" + price.toFixed(2));
            
            // Variants
            if(variants && variants.length > 0) {
                $("#m-variants-box").removeClass("d-none");
                let html = "";
                variants.forEach(v => {
                    html += `<input type="radio" class="btn-check" name="food_var" id="var_${v.id}" value="${v.id}" data-price="${v.price}" onchange="updateMTotal()">
                             <label class="btn btn-outline-dark rounded-pill py-2 text-start px-3" for="var_${v.id}">${v.name} <span class="float-end fw-bold">+$${v.price}</span></label>`;
                });
                $("#m-variants-list").html(html);
            } else {
                $("#m-variants-box").addClass("d-none");
            }

            // Extras
            if(extras && extras.length > 0) {
                $("#m-extras-box").removeClass("d-none");
                let html = "";
                extras.forEach(e => {
                    html += `<div class="col-6">
                                <input type="checkbox" class="btn-check m-extra-cb" id="ext_${e.id}" value="${e.id}" data-price="${e.price}" data-name="${e.name}" onchange="updateMTotal()">
                                <label class="btn btn-outline-secondary rounded-pill py-2 w-100 text-start px-3" for="ext_${e.id}">${e.name} <br> <span class="fw-bold small">+$${e.price}</span></label>
                             </div>`;
                });
                $("#m-extras-list").html(html);
            } else {
                $("#m-extras-box").addClass("d-none");
            }

            updateMTotal();
            new bootstrap.Modal(document.getElementById("foodModal")).show();
        }

        function updateMTotal() {
            let base = currentItem.price;
            let varPrice = parseFloat($("input[name=\'food_var\']:checked").data("price") || 0);
            let extPrice = 0;
            $(".m-extra-cb:checked").each(function(){ extPrice += parseFloat($(this).data("price")); });
            $("#m-total").text((base + varPrice + extPrice).toFixed(2));
        }

        function addToCart() {
            let varId = $("input[name=\'food_var\']:checked").val();
            let extras = [];
            $(".m-extra-cb:checked").each(function(){
                extras.push({ id: $(this).val(), price: $(this).data("price"), name: $(this).data("name") });
            });
            
            cart.push({
                item_id: currentItem.id,
                name: currentItem.name,
                price: parseFloat($("#m-total").text()),
                variant_id: varId,
                extras: extras
            });

            bootstrap.Modal.getInstance(document.getElementById("foodModal")).hide();
            renderCartSummary();
        }

        function renderCartSummary() {
            if(cart.length > 0) {
                $("#cart-float").css("display", "flex");
                $("#cart-count").text(cart.length);
                let total = cart.reduce((acc, obj) => acc + obj.price, 0);
                $("#cart-total").text(total.toFixed(2));
            }
        }

        function goToCheckout() {
            alert("Redirecting to checkout... Orders will be sent to the In-Shop Admin Panel!");
        }
    </script>
</body>
</html>';

file_put_contents("$dirV/welcome.blade.php", $content);
echo "Mobile responsive customer web page created.\n";

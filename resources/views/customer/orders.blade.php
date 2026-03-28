<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Orders - Fast Food Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { background-color: #f8f9fa; font-family: "Outfit", sans-serif; color: var(--dark); padding-bottom: 90px; }
        .order-card { border-radius: 25px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; background: white; }
        .status-badge { font-size: 0.75rem; padding: 6px 15px; border-radius: 30px; font-weight: bold; }
        .btn-primary { border-radius: 30px; padding: 10px 25px; font-weight: bold; background: var(--primary); border-color: var(--primary); }
        
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; height: 75px; display: flex; align-items: center; justify-content: space-around; border-top: 1px solid #eee; border-radius: 30px 30px 0 0; box-shadow: 0 -5px 25px rgba(0,0,0,0.05); z-index: 1050; padding: 0 10px; }
        .nav-item { display: flex; flex-direction: column; align-items: center; justify-content: center; color: #999; text-decoration: none; font-size: 11px; font-weight: 600; width: 60px; height: 60px; transition: 0.3s; }
        .nav-item.active { color: var(--primary); }
        .nav-item i { font-size: 18px; margin-bottom: 4px; }
        
        .loader-dots { display: flex; align-items: center; justify-content: center; padding: 30px 0; display: none; }
        .loader-dots div { width: 8px; height: 8px; margin: 0 4px; background: var(--primary); border-radius: 50%; animation: loader-dots 0.6s infinite alternate; }
        .loader-dots div:nth-child(2) { animation-delay: 0.2s; }
        .loader-dots div:nth-child(3) { animation-delay: 0.4s; }
        @keyframes loader-dots { from { opacity: 1; transform: scale(1); } to { opacity: 0.3; transform: scale(1.5); } }
    </style>
</head>
<body>
    <!-- Bottom App Navigation -->
    <div class="bottom-nav">
        <a href="{{ url('/') }}" class="nav-item">
            <i class="fas fa-home-alt"></i>
            <span>Menu</span>
        </a>
        <a href="{{ route('customer.orders') }}" class="nav-item active">
            <i class="fas fa-shopping-bag"></i>
            <span>My Orders</span>
        </a>
        @if(isset($customer))
            <a href="#" class="nav-item text-primary" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
                <span>Account</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mb-3">
                <li class="px-3 py-2 fw-bold small text-muted text-uppercase" style="font-size: 10px;">Welcome, {{ $customer->name }}</li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('customer.logout') }}" onclick="localStorage.removeItem('customer_device_token')"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        @endif
    </div>

    <div class="container py-4">
        <div class="mb-4">
            <h2 class="fw-800" style="letter-spacing: -1px;">My Orders</h2>
            <p class="text-muted small">Swipe down to see more history</p>
        </div>

        <div id="orders-container">
            @if($orders->count() > 0)
                @include('customer.partials.order_cards')
            @else
                <div class="text-center py-5">
                    <div class="mb-4"><i class="fas fa-receipt fa-4x text-muted opacity-25"></i></div>
                    <h4 class="fw-bold">No orders yet</h4>
                    <p class="text-muted">Treat yourself to something delicious!</p>
                    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Order Something Now</a>
                </div>
            @endif
        </div>

        <!-- Infinite Scroll Loader -->
        <div class="loader-dots" id="scroll-loader">
            <div></div><div></div><div></div>
        </div>
        
        <div id="no-more-history" class="text-center py-4 text-muted small d-none">
            <i class="fas fa-check-circle me-1"></i> You've seen all your orders
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let page = 1;
        let loading = false;
        let hasMore = @if($orders->hasMorePages()) true @else false @endif;

        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
                if (!loading && hasMore) {
                    loadMoreOrders();
                }
            }
        });

        function loadMoreOrders() {
            page++;
            loading = true;
            $("#scroll-loader").fadeIn();

            $.ajax({
                url: "?page=" + page,
                type: "get",
                beforeSend: function() {
                    $("#scroll-loader").show();
                }
            }).done(function(data) {
                if (data.trim().length === 0) {
                    hasMore = false;
                    $("#scroll-loader").fadeOut();
                    $("#no-more-history").removeClass("d-none");
                    return;
                }
                $("#scroll-loader").fadeOut();
                $("#orders-container").append(data);
                loading = false;
            }).fail(function(jqXHR, ajaxOptions, thrownError) {
                console.log('Server error...');
                $("#scroll-loader").fadeOut();
                loading = false;
            });
        }
    </script>
</body>
</html>

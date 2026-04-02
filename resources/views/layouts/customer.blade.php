<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Fast Food Hub')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { background-color: #f8f9fa; font-family: "Outfit", sans-serif; color: var(--dark); padding-bottom: 90px; }
        .btn-primary { border-radius: 30px; padding: 10px 25px; font-weight: bold; background: var(--primary); border-color: var(--primary); }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); height: 75px; display: flex; align-items: center; justify-content: space-around; border-top: 1px solid #eee; border-radius: 30px 30px 0 0; box-shadow: 0 -5px 25px rgba(0,0,0,0.05); z-index: 1050; padding: 0 10px; }
        .nav-item { display: flex; flex-direction: column; align-items: center; justify-content: center; color: #999; text-decoration: none; font-size: 11px; font-weight: 600; width: 60px; height: 60px; transition: 0.3s; }
        .nav-item.active { color: var(--primary); }
        .nav-item i { font-size: 18px; margin-bottom: 4px; }
        .rounded-4 { border-radius: 1.5rem!important; }
        @yield('styles')
    </style>
</head>
<body>
    <div class="bottom-nav">
        <a href="{{ url('/') }}" class="nav-item">
            <i class="fas fa-home-alt"></i>
            <span>Menu</span>
        </a>
        <a href="{{ route('customer.orders') }}" class="nav-item {{ Route::is('customer.orders') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i>
            <span>My Orders</span>
        </a>
        <a href="{{ route('customer.profile') }}" class="nav-item {{ Route::is('customer.profile') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </div>

    <div class="container py-4">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

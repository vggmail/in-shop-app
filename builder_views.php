<?php

$dirV = __DIR__ . '/resources/views/admin';
$dirs = [
    $dirV,
    "$dirV/products",
    "$dirV/customers",
    "$dirV/coupons",
    "$dirV/orders",
    "$dirV/pos",
    "$dirV/payments",
    "$dirV/reports",
];

foreach ($dirs as $d) {
    if (!is_dir($d)) mkdir($d, 0777, true);
}

// Layout
file_put_contents(__DIR__ . "/resources/views/layouts/admin.blade.php", '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background-color: #f8f9fc; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; padding-top: 20px; color: white; }
        .sidebar a { color: #ecf0f1; text-decoration: none; display: block; padding: 10px 20px; border-bottom: 1px solid #34495e; }
        .sidebar a:hover { background-color: #34495e; }
        .main-content { padding: 20px; }
    </style>
    @yield("styles")
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">In-Shop POS</h4>
                <a href="{{ route("dashboard") }}"><i class="fas fa-fw fa-tachometer-alt"></i> Dashboard</a>
                <a href="{{ route("pos.index") }}"><i class="fas fa-fw fa-cash-register"></i> POS Screen</a>
                <a href="{{ route("products.index") }}"><i class="fas fa-fw fa-box"></i> Products</a>
                <a href="{{ route("orders.index") }}"><i class="fas fa-fw fa-shopping-cart"></i> Orders</a>
                <a href="{{ route("customers.index") }}"><i class="fas fa-fw fa-users"></i> Customers</a>
                <a href="{{ route("coupons.index") }}"><i class="fas fa-fw fa-tags"></i> Coupons</a>
                <a href="{{ route("payments.index") }}"><i class="fas fa-fw fa-credit-card"></i> Payments</a>
                <a href="{{ route("reports.index") }}"><i class="fas fa-fw fa-chart-bar"></i> Reports</a>
                <hr>
                <form action="{{ route("logout") }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-white text-decoration-none px-4"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
            <div class="col-md-10 main-content">
                @if(session("success"))
                    <div class="alert alert-success">{{ session("success") }}</div>
                @endif
                @yield("content")
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield("scripts")
</body>
</html>');

// Dashboard
file_put_contents("$dirV/dashboard.blade.php", '@extends("layouts.admin")
@section("content")
<h2 class="mb-4">Dashboard</h2>
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Today\'s Sales</h5>
                <h3 class="card-text">${{ number_format($todaySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h3 class="card-text">${{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h3 class="card-text">{{ $totalOrders }}</h3>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Low Stock / Top Products</div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($topProducts as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            {{ $p->name }} <span class="badge bg-danger rounded-pill">{{ $p->stock_quantity }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection');

echo "Base Views Generated.\n";

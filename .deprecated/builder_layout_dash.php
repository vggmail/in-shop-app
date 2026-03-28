<?php
$dirV = __DIR__ . '/resources/views/admin';

// Layout
file_put_contents(__DIR__ . "/resources/views/layouts/admin.blade.php", '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: #c2c7d0; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar a { color: #c2c7d0; text-decoration: none; display: block; padding: 12px 20px; font-weight: 500; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .main-content { padding: 20px; }
    </style>
    @yield("styles")
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            <div class="col-auto col-md-3 col-xl-2 sidebar">
                <h4 class="text-center my-4 text-white"><i class="fas fa-utensils text-danger"></i> Fast Food</h4>
                <a href="{{ route("dashboard") }}"><i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="{{ route("pos.index") }}" class="text-warning"><i class="fas fa-fw fa-cash-register me-2"></i> POS Screen</a>
                <a href="{{ route("items.index") }}"><i class="fas fa-fw fa-hamburger me-2"></i> Menu Items</a>
                <a href="{{ route("orders.index") }}"><i class="fas fa-fw fa-receipt me-2"></i> Orders</a>
                <a href="{{ route("customers.index") }}"><i class="fas fa-fw fa-users me-2"></i> Customers</a>
                <a href="{{ route("coupons.index") }}"><i class="fas fa-fw fa-ticket-alt me-2"></i> Coupons</a>
                <a href="{{ route("expenses.index") }}"><i class="fas fa-fw fa-wallet me-2"></i> Expenses</a>
                <a href="{{ route("payments.index") }}"><i class="fas fa-fw fa-credit-card me-2"></i> Payments</a>
                <a href="{{ route("reports.index") }}"><i class="fas fa-fw fa-chart-line me-2"></i> Reports</a>
                <hr class="border-secondary">
                <form action="{{ route("logout") }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-white text-decoration-none px-4"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
            <div class="col main-content">
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
        <div class="card bg-primary text-white shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Today\'s Sales</h5>
                <h3 class="card-text">${{ number_format($todaySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h3 class="card-text">${{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-info text-white shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h3 class="card-text">{{ $totalOrders }}</h3>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold pt-3 pb-3">Top Selling Items</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($topItems as $i)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $i->name }} <span class="badge bg-primary rounded-pill">Available</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection');

echo "Layout generated.\n";

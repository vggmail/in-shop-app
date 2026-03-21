@extends("layouts.admin")
@section("content")
<h2 class="mb-4">Dashboard</h2>
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Today's Sales</h5>
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
@endsection
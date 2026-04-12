@extends("layouts.admin")

@section("styles")
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .stat-card { border: none; border-radius: 24px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: #ffffff; border: 1px solid rgba(0,0,0,0.03); }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .icon-box { width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; }
    .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
    .card-header-custom { background: transparent; border: none; padding: 1.5rem 1.5rem 0.5rem; }
    .chart-container { position: relative; height: 350px; width: 100%; }
</style>
@endsection

@section("content")
<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col d-flex align-items-center">
            <div>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 fw-800 text-dark me-3">Command Center</h2>
                    <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
                        <a href="{{ route('dashboard', ['tab' => 'classic']) }}" class="btn rounded-pill px-4 {{ $tab === 'classic' ? 'btn-primary text-white pointer-events-none' : 'btn-light text-muted' }}">Standard View</a>
                        <a href="{{ route('dashboard', ['tab' => 'analytics']) }}" class="btn rounded-pill px-4 {{ $tab === 'analytics' || !isset($tab) ? 'btn-primary text-white pointer-events-none' : 'btn-light text-muted' }}">Advanced Analytics ✨</a>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-2">Analytics overview for your restaurant network</p>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <a href="?tab=classic&range=30d" class="btn btn-light border px-4 py-2 small fw-600 {{ $range == '30d' ? 'active btn-primary text-white' : '' }}">Last 30 Days</a>
                <a href="?tab=classic&range=6m" class="btn btn-light border px-4 py-2 small fw-600 {{ $range == '6m' ? 'active btn-primary text-white' : '' }}">Last 6 Months</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box bg-gradient-danger text-white shadow-lg"><i class="fas fa-bolt fa-lg"></i></div>
                    <span class="text-success small fw-bold mt-1">+12%</span>
                </div>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Today Sales</h6>
                <h3 class="fw-800 mb-0">&#8377;{{ number_format($todaySales, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box bg-gradient-primary text-white shadow-lg"><i class="fas fa-shopping-cart fa-lg"></i></div>
                </div>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Volume</h6>
                <h3 class="fw-800 mb-0">{{ $totalOrders }} <small class="text-muted fs-6 fw-normal">Orders</small></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box bg-gradient-success text-white shadow-lg"><i class="fas fa-user-friends fa-lg"></i></div>
                </div>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Client Base</h6>
                <h3 class="fw-800 mb-0">{{ $totalCustomers }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box bg-gradient-warning text-white shadow-lg"><i class="fas fa-coins fa-lg"></i></div>
                </div>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Gross Revenue</h6>
                <h3 class="fw-800 mb-0">&#8377;{{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card stat-card shadow-sm h-100 overflow-hidden">
                <div class="card-header-custom">
                    <h5 class="fw-800 mb-0">Sales Trajectory</h5>
                    <p class="text-muted small mb-0">Daily revenue trends at a glance</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card stat-card shadow-sm h-100 p-4">
                <h5 class="fw-800 mb-3">Leaderboard</h5>
                <div class="list-group list-group-flush border-0 mt-3">
                    @foreach($topItems as $index => $ti)
                    <div class="list-group-item px-0 border-0 mb-3 p-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="fw-700 mb-0 text-dark">{{ $ti->item ? $ti->item->name : 'Item Deleted (#' . $ti->item_id . ')' }} {{ ($ti->item && $ti->item->default_size) ? '- ' . $ti->item->default_size : '' }}</h6>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary rounded-pill" style="width: {{ ($ti->total / ($topItems->first()->total ?: 1)) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="ms-3 text-end"><span class="fw-800 text-dark d-block">{{ $ti->total }}</span><small class="text-muted">Orders</small></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailySales->pluck('date')->map(fn($d) => date('d M', strtotime($d)))) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($dailySales->pluck('total')) !!},
                borderColor: '#6366f1',
                borderWidth: 4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 4,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    callbacks: { label: (c) => `₹${c.parsed.y.toLocaleString()}` }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => '₹' + v } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endsection


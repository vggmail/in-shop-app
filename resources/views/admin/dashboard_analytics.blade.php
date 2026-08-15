@extends("layouts.admin")

@section("styles")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 20px; border: 1px solid rgba(0,0,0,0.04); }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.06) !important; }
    .icon-wrapper { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
    .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
    .chart-container-large { height: 400px; position: relative; width: 100%; }
    .chart-container-small { height: 320px; position: relative; width: 100%; }
</style>
@endsection

@section("content")

<div class="d-flex flex-column flex-md-row justify-content-between mb-4 mt-2 no-print gap-3 gap-md-0">
    <div class="d-flex align-items-center">
        <h2 class="mb-0 me-3"><i class="fas fa-rocket text-primary me-2"></i> Command Center</h2>
        <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
            <a href="{{ route('dashboard', ['tab' => 'classic']) }}" class="btn rounded-pill px-4 {{ $tab === 'classic' ? 'btn-primary text-white pointer-events-none' : 'btn-white text-muted' }}">Standard View</a>
            <a href="{{ route('dashboard', ['tab' => 'analytics']) }}" class="btn rounded-pill px-4 {{ $tab === 'analytics' || !isset($tab) ? 'btn-primary text-white pointer-events-none' : 'btn-white text-muted' }}">Advanced Analytics ✨</a>
        </div>
    </div>
    <div class="d-flex gap-2 bg-white rounded-pill p-1 shadow-sm border">
        <a href="?tab=analytics&range=7" class="btn rounded-pill px-3 py-1 {{ $range == '7' ? 'btn-dark text-white fw-bold pointer-events-none' : 'btn-link text-muted text-decoration-none' }}">7 Days</a>
        <a href="?tab=analytics&range=30" class="btn rounded-pill px-3 py-1 {{ $range == '30' ? 'btn-dark text-white fw-bold pointer-events-none' : 'btn-link text-muted text-decoration-none' }}">30 Days</a>
        <a href="?tab=analytics&range=90" class="btn rounded-pill px-3 py-1 {{ $range == '90' ? 'btn-dark text-white fw-bold pointer-events-none' : 'btn-link text-muted text-decoration-none' }}">90 Days</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-md-3">
        <div class="card bg-white stat-card h-100 p-4 shadow-sm">
            <div class="d-flex justify-content-between mb-3">
                <div class="icon-wrapper shadow-sm bg-gradient-success text-white">
                    <i class="fas fa-coins fa-lg"></i>
                </div>
            </div>
            <p class="text-muted text-uppercase fw-bold small mb-1 tracking-wider opacity-75">Gross Revenue</p>
            <h3 class="fw-800 text-dark mb-0">&#8377;{{ number_format($grossSales, 2) }}</h3>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="col-md-3">
        <div class="card bg-white stat-card h-100 p-4 shadow-sm">
            <div class="d-flex justify-content-between mb-3">
                <div class="icon-wrapper shadow-sm bg-gradient-danger text-white">
                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                </div>
            </div>
            <p class="text-muted text-uppercase fw-bold small mb-1 tracking-wider opacity-75">Total Expenses</p>
            <h3 class="fw-800 text-dark mb-0">&#8377;{{ number_format($totalExpenses, 2) }}</h3>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="col-md-3">
        <div class="card bg-white stat-card h-100 p-4 shadow-sm">
            <div class="d-flex justify-content-between mb-3">
                <div class="icon-wrapper shadow-sm bg-gradient-primary text-white">
                    <i class="fas fa-chart-line fa-lg"></i>
                </div>
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold fs-xs align-self-start">Margin: {{ $grossSales > 0 ? round((($grossSales - $totalExpenses) / $grossSales) * 100) : 0 }}%</span>
            </div>
            <p class="text-muted text-uppercase fw-bold small mb-1 tracking-wider opacity-75">Net Profit</p>
            <h3 class="fw-800 text-dark mb-0">&#8377;{{ number_format($netProfit, 2) }}</h3>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="col-md-3">
        <div class="card bg-white stat-card h-100 p-4 shadow-sm">
            <div class="d-flex justify-content-between mb-3">
                <div class="icon-wrapper shadow-sm bg-gradient-info text-white">
                    <i class="fas fa-shopping-bag fa-lg"></i>
                </div>
            </div>
            <p class="text-muted text-uppercase fw-bold small mb-1 tracking-wider opacity-75">Total Orders</p>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($orderCount) }}</h3>
            @if($orderCount > 0)
            <p class="small text-muted mb-0 mt-2">Avg. Ticket: <span class="fw-bold text-dark">&#8377;{{ number_format($grossSales / $orderCount, 2) }}</span></p>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Performance Trajectory Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-800 mb-0">Financial Performance</h5>
                <p class="text-muted small">Daily revenue vs expenses over the last {{ $range }} days</p>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="chart-container-large">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Doughnut -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-800 mb-0">Top Performing Items</h5>
                <p class="text-muted small">By quantity sold</p>
            </div>
            <div class="card-body px-4 pb-4 pt-2 d-flex flex-column justify-content-center">
                @if(count($topItemLabels) > 0)
                <div class="chart-container-small">
                    <canvas id="itemsChart"></canvas>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                    <p class="text-muted">No sales data available for this period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section("scripts")
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gradient definitions
    const canvas = document.getElementById('performanceChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Revenue Gradient
    const revGradient = ctx.createLinearGradient(0, 0, 0, 400);
    revGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); // Emerald
    revGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

    // Expenses Gradient
    const expGradient = ctx.createLinearGradient(0, 0, 0, 400);
    expGradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)'); // Red
    expGradient.addColorStop(1, 'rgba(239, 68, 68, 0)');

    const chartDates = {!! json_encode($chartDates) !!};
    const salesData = {!! json_encode($salesData) !!};
    const expensesData = {!! json_encode($expensesData) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartDates,
            datasets: [
                {
                    label: 'Gross Revenue',
                    data: salesData,
                    borderColor: '#10b981',
                    backgroundColor: revGradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: expensesData,
                    borderColor: '#ef4444',
                    backgroundColor: expGradient,
                    borderWidth: 3,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#ef4444',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    titleFont: { size: 13, family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { family: "'Inter', sans-serif", weight: '600' }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 10, font: { family: "'Inter', sans-serif" } }
                },
                y: {
                    grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false },
                    ticks: {
                        callback: function(value) { return '₹' + (value >= 1000 ? (value/1000).toFixed(1) + 'k' : value); },
                        font: { family: "'Inter', sans-serif" }
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // Doughnut Chart for Top Items
    @if(count($topItemLabels) > 0)
    const ctxPie = document.getElementById('itemsChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($topItemLabels) !!},
            datasets: [{
                data: {!! json_encode($topItemData) !!},
                backgroundColor: [
                    '#6366f1', // Indigo
                    '#3b82f6', // Blue
                    '#0ea5e9', // Sky
                    '#06b6d4', // Cyan
                    '#14b8a6'  // Teal
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: { family: "'Inter', sans-serif", size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.parsed + ' items sold';
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection

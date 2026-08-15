@extends('layouts.admin')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('bar.wastage.index') }}" class="btn btn-link text-decoration-none p-0 mb-1 d-inline-flex align-items-center">
            <i class="fas fa-arrow-left me-1"></i> Back to Bar Console
        </a>
        <h2 class="mb-0 text-dark">Bar Excise & Consumption Report</h2>
        <p class="text-muted">Analyze live stock registers, shift sales, and spillage audits.</p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-light border fw-bold">
            <i class="fas fa-print me-1"></i> Print Register
        </button>
    </div>
</div>

<!-- Key Stat Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase small text-white-50 fw-bold">Total Liquor Stock Value</h6>
                    <h3 class="mb-0 fw-bold">₹{{ number_format($liquors->sum(fn($l) => $l->stock_quantity * $l->cost_per_unit), 2) }}</h3>
                </div>
                <div class="fs-1 text-white-50"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase small text-white-50 fw-bold">Liquor Brands Registered</h6>
                    <h3 class="mb-0 fw-bold">{{ $liquors->count() }} Brands</h3>
                </div>
                <div class="fs-1 text-white-50"><i class="fas fa-cocktail"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase small text-dark-50 fw-bold">Low Stock Warning</h6>
                    <h3 class="mb-0 fw-bold">{{ $liquors->where('stock_quantity', '<=', 'min_stock_level')->count() }} Brands</h3>
                </div>
                <div class="fs-1 text-dark-50"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Live Alcohol Inventory Register -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-wine-bottle text-primary me-2"></i>Live Liquor Stock Register</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>Brand Name</th>
                                <th>Bottle Size (ml)</th>
                                <th>Stock (Bottles)</th>
                                <th>Stock Volume (ml)</th>
                                <th>Cost Value</th>
                                <th>Min Alert Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liquors as $l)
                            @php
                                $stockMl = floatval($l->stock_quantity) * floatval($l->bottle_size_ml ?? 750);
                                $stockVal = floatval($l->stock_quantity) * floatval($l->cost_per_unit);
                                $isLow = floatval($l->stock_quantity) <= floatval($l->min_stock_level);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $l->name }}</div>
                                </td>
                                <td>{{ floatval($l->bottle_size_ml) }} ml</td>
                                <td>
                                    <span class="fw-bold fs-6 {{ $isLow ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($l->stock_quantity, 2) }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary">{{ number_format($stockMl) }} ml</span></td>
                                <td class="fw-semibold">₹{{ number_format($stockVal, 2) }}</td>
                                <td>{{ number_format($l->min_stock_level, 2) }}</td>
                                <td>
                                    @if($isLow)
                                        <span class="badge bg-danger text-white">Reorder Now</span>
                                    @else
                                        <span class="badge bg-success text-white">Stock Good</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <p class="mb-0">No alcohol ingredients registered. Go to Ingredient Master to add some!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Consumption Log -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line text-success me-2"></i>Excise Consumption Summary (Monthly Overview)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>Liquor Brand</th>
                                <th class="text-center">Total Sales Deductions</th>
                                <th class="text-center">Spills & Leaks</th>
                                <th class="text-center">Bottle Breakage</th>
                                <th class="text-center">Complimentary / Free Pours</th>
                                <th class="text-center bg-light fw-bold">Grand Total Consumption</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liquors as $l)
                            @php
                                $saleQty = $salesConsumption[$l->id]->total_units_sold ?? 0.0;
                                $stats = $wastageStats[$l->id] ?? null;
                                $spill = $stats->total_spill ?? 0.0;
                                $breakage = $stats->total_breakage ?? 0.0;
                                $otherWastage = floatval($stats->total_free_pour ?? 0) + floatval($stats->total_complimentary ?? 0);
                                $totalCons = floatval($saleQty) + floatval($spill) + floatval($breakage) + floatval($otherWastage);
                                $bottleSize = floatval($l->bottle_size_ml) ?: 750.0;
                            @endphp
                            <tr>
                                <td class="fw-bold text-dark">{{ $l->name }}</td>
                                <td class="text-center">{{ number_format($saleQty, 2) }} bottles <br><span class="text-muted small">({{ number_format($saleQty * $bottleSize) }} ml)</span></td>
                                <td class="text-center text-warning">{{ number_format($spill, 2) }} bottles <br><span class="text-muted small">({{ number_format($spill * $bottleSize) }} ml)</span></td>
                                <td class="text-center text-danger">{{ number_format($breakage, 2) }} bottles <br><span class="text-muted small">({{ number_format($breakage * $bottleSize) }} ml)</span></td>
                                <td class="text-center text-info">{{ number_format($otherWastage, 2) }} bottles <br><span class="text-muted small">({{ number_format($otherWastage * $bottleSize) }} ml)</span></td>
                                <td class="text-center bg-light fw-bold text-dark fs-6">{{ number_format($totalCons, 2) }} bottles <br><span class="text-muted small">({{ number_format($totalCons * $bottleSize) }} ml)</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <p class="mb-0">No records to display.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

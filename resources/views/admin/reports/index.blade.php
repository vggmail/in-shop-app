@extends("layouts.admin")

@section("styles")
<style>
    @media print {
        /* Hide everything outside the report content */
        nav, aside, header, footer,
        .sidebar, .navbar, .topbar,
        [class*="sidebar"], [class*="navbar"],
        [class*="nav-"], .dropdown-menu,
        .btn, button, input,
        .no-print { display: none !important; }

        body { background: white !important; margin: 0 !important; padding: 0 !important; }

        /* Remove card shadows and borders for cleaner print */
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }

        /* Print header */
        .print-header { display: block !important; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .print-header h3 { margin: 0; font-size: 20px; font-weight: bold; }
        .print-header p { margin: 2px 0; font-size: 12px; color: #555; }

        /* Full width content */
        .col-md-3 { width: 25% !important; float: left; }
        .row { display: flex !important; flex-wrap: wrap; }
        main, .main-content, [class*="content"] { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    }

    /* Hide print header on screen */
    .print-header { display: none; }
</style>
@endsection

@section("content")

{{-- Hidden print header shown only when printing --}}
<div class="print-header">
    <h3>FAST FOOD HUB — Analytical Report</h3>
    <p>Generated on {{ date('d M Y, h:i A') }}</p>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4 mt-2 no-print gap-3 gap-md-0">
    <div class="d-flex align-items-center">
        <h2 class="mb-0 me-3"><i class="fas fa-chart-pie text-primary"></i> Analytical Reports</h2>
        <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
            <a href="{{ route('reports.index', ['tab' => 'classic']) }}" class="btn rounded-pill px-4 {{ $tab === 'classic' || !isset($tab) ? 'btn-primary text-white pointer-events-none' : 'btn-white text-muted' }}">Standard View</a>
            <a href="{{ route('reports.index', ['tab' => 'analytics']) }}" class="btn rounded-pill px-4 {{ isset($tab) && $tab === 'analytics' ? 'btn-primary text-white pointer-events-none' : 'btn-white text-muted' }}">Advanced Analytics ✨</a>
        </div>
    </div>
    <div class="d-flex gap-2">
        <input type="date" id="report_date" class="form-control w-auto" value="{{ date("Y-m-d") }}">
        <button class="btn btn-dark text-nowrap" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Gross Sales</small>
            <h3 class="fw-bold mb-0 text-success">&#8377;{{ number_format(\App\Models\Order::sum("grand_total"), 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Total Expenses</small>
            <h3 class="fw-bold mb-0 text-danger">&#8377;{{ number_format(\App\Models\Expense::sum("amount"), 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Net Profit</small>
            <h3 class="fw-bold mb-0 text-primary">&#8377;{{ number_format(\App\Models\Order::sum("grand_total") - \App\Models\Expense::sum("amount"), 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Order Count</small>
            <h3 class="fw-bold mb-0 text-dark">{{ \App\Models\Order::count() }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold py-3"><i class="fas fa-list me-2"></i> Recent Orders Breakdown</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="min-width: 600px;">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Order #</th>
                    <th>Type</th>
                    <th>Payment</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Order::latest()->take(10)->get() as $o)
                <tr>
                    <td>{{ $o->created_at->format("d M, Y") }}</td>
                    <td>{{ $o->order_number }}</td>
                    <td><span class="badge border text-dark">{{ $o->order_type }}</span></td>
                    <td>{{ $o->payment_method }}</td>
                    <td class="text-end fw-bold">&#8377;{{ number_format($o->grand_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3"><i class="fas fa-wallet me-2"></i> Recent Expenses Breakdown</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="min-width: 600px;">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Expense::latest()->take(10)->get() as $e)
                <tr>
                    <td>{{ date("d M, Y", strtotime($e->date)) }}</td>
                    <td><span class="badge bg-light text-danger">{{ $e->category }}</span></td>
                    <td>{{ $e->description }}</td>
                    <td class="text-end fw-bold">-&#8377;{{ number_format($e->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

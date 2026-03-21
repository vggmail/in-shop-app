@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <h2><i class="fas fa-chart-pie text-primary"></i> Analytical Reports</h2>
    <div class="d-flex gap-2">
        <input type="date" id="report_date" class="form-control" value="{{ date("Y-m-d") }}">
        <button class="btn btn-dark" onclick="window.print()"><i class="fas fa-print"></i></button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Gross Sales</small>
            <h3 class="fw-bold mb-0 text-success">${{ number_format(\App\Models\Order::sum("grand_total"), 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Total Expenses</small>
            <h3 class="fw-bold mb-0 text-danger">${{ number_format(\App\Models\Expense::sum("amount"), 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted fw-bold text-uppercase">Net Profit</small>
            <h3 class="fw-bold mb-0 text-primary">${{ number_format(\App\Models\Order::sum("grand_total") - \App\Models\Expense::sum("amount"), 2) }}</h3>
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
        <table class="table table-hover mb-0">
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
                    <td class="text-end fw-bold">${{ number_format($o->grand_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3"><i class="fas fa-wallet me-2"></i> Recent Expenses Breakdown</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
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
                    <td class="text-end fw-bold">-${{ number_format($e->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <a href="{{ route('shifts.index') }}" class="btn btn-link text-decoration-none p-0 mb-2">
        <i class="fas fa-arrow-left me-1"></i> Back to Shifts
    </a>
    <h2 class="mb-0">Closing Shift Audit</h2>
    <p class="text-muted">Review the sales summary and enter the final cash count in your drawer.</p>
</div>

<div class="row g-4">
    <!-- Sales Summary -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Shift Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Shift Started</label>
                            <div class="h5 mb-0">{{ $shift->opened_at->format('d M, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Opened By</label>
                            <div class="h5 mb-0">{{ $shift->user->name }}</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-5">

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-0">
                                    <div class="fw-bold">Opening Balance</div>
                                    <div class="small text-muted">Initial cash float in drawer</div>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($shift->opening_balance, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0">
                                    <div class="fw-bold text-success">Total Cash Sales (+)</div>
                                    <div class="small text-muted">Total cash collected from orders</div>
                                </td>
                                <td class="text-end fw-bold text-success">+₹{{ number_format($cashSales, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0">
                                    <div class="fw-bold text-primary">Petty Cash In (+)</div>
                                    <div class="small text-muted">Extra cash added to drawer during shift</div>
                                </td>
                                <td class="text-end fw-bold text-primary">+₹{{ number_format($pettyIn, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0">
                                    <div class="fw-bold text-danger">Petty Cash Out (-)</div>
                                    <div class="small text-muted">Expenses paid from the drawer</div>
                                </td>
                                <td class="text-end fw-bold text-danger">-₹{{ number_format($pettyOut, 2) }}</td>
                            </tr>
                            <tr class="border-top border-dark">
                                <td class="ps-0 py-3">
                                    <div class="h5 mb-0 fw-bold">System Expected Cash</div>
                                    <div class="small text-muted">Amount that should be in the drawer</div>
                                </td>
                                <td class="text-end h4 mb-0 fw-bold text-dark">₹{{ number_format($expected, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Closing Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100 bg-info-soft border-info">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-info">Final Audit</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('shifts.close') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Actual Cash In Drawer (₹)</label>
                        <div class="input-group input-group-lg border-info border rounded-3 overflow-hidden shadow-sm">
                            <span class="input-group-text bg-white border-0">₹</span>
                            <input type="number" step="0.01" name="closing_balance" class="form-control border-0 shadow-none fw-bold" required placeholder="0.00" autofocus>
                        </div>
                        <p class="mt-2 small text-muted">Physically count the cash in your drawer and enter the total here.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Audit Note (Optional)</label>
                        <textarea name="closing_note" class="form-control border-0" rows="3" placeholder="Any reasons for discrepancy?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-info text-white w-100 py-3 fw-bold rounded-3 mt-2 shadow" onclick="return confirm('Are you sure you want to close this shift?')">
                        <i class="fas fa-check-circle me-2"></i> COMPLETE AUDIT & CLOSE
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-info-soft { background-color: #f0f9ff; }
    .border-info { border: 1px solid #bae6fd !important; }
</style>
@endsection

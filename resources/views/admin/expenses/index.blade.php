@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-wallet text-danger me-2"></i> Operational Expenses</h2>
        <p class="text-muted small">Monitor your store expenditures and raw material costs.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="fas fa-plus small me-2"></i> Log Expenditure</button>
    </div>
</div>

<div class="row">
    @foreach($expenses->groupBy('category') as $cat => $group)
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <span class="badge border text-dark fw-bold rounded-pill px-3 py-2 bg-light"><i class="fas fa-tag me-1 text-primary"></i> {{ $cat }}</span>
                <span class="fw-bold text-danger">-₹{{ number_format($group->sum('amount'), 2) }}</span>
            </div>
            <div class="card-body px-0">
                <div class="list-group list-group-flush">
                    @foreach($group->take(3) as $e)
                    <div class="list-group-item bg-transparent py-3 px-4 border-light d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-bold small">{{ $e->description ?: "Unspecified Item" }}</p>
                            <small class="text-muted">{{ date("d M, Y", strtotime($e->date)) }}</small>
                        </div>
                        <span class="text-secondary small fw-bold">-₹{{ number_format($e->amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-3 px-4 text-center">
                <a href="#" class="small text-decoration-none fw-bold text-primary">View Full Category History <i class="fas fa-chevron-right small ms-1"></i></a>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($expenses->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="fas fa-money-bill-wave fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No expenses recorded yet.</h5>
    </div>
    @endif
</div>

<div class="modal fade" id="addExpenseModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form action="{{ route('expenses.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Log New Expenditure</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Expense Type</label>
        <select name="category" class="form-select form-select-lg mb-3 shadow-none bg-light border-0" required>
            <option value="Rent">Rent</option>
            <option value="Salary">Salary Staff</option>
            <option value="Raw Material">Inventory / Raw Materials</option>
            <option value="Electricity">Bills / Electricity / Internet</option>
            <option value="Repair">Maintenance & Repairs</option>
            <option value="Marketing">Marketing & Ads</option>
            <option value="Other">Other Expenses</option>
        </select>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Expended Amount</label>
        <div class="input-group mb-3">
            <span class="input-group-text bg-white border-0 fw-bold">$</span>
            <input type="number" step="0.01" name="amount" class="form-control form-control-lg bg-light border-0 shadow-none text-danger fw-bold" placeholder="0.00" required>
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Date of Payment</label>
        <input type="date" name="date" class="form-control form-control-lg mb-3 shadow-none bg-light border-0" required value="{{ date("Y-m-d") }}">
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Expense Information</label>
        <textarea name="description" class="form-control form-control-lg shadow-none bg-light border-0" rows="3" placeholder="Additional details (Vendor name, Check number)..."></textarea>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4">
        <button type="submit" class="btn btn-danger btn-lg w-100 py-3 rounded-3 fw-bold shadow-sm">CONFIRM EXPENDITURE</button>
    </div>
</form></div></div></div>
@endsection


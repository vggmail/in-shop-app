@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3 mt-2">
    <h2><i class="fas fa-ticket-alt text-danger me-2"></i> Promotional Coupons</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal"><i class="fas fa-plus"></i> New Coupon</button>
</div>

<div class="row">
    @foreach($coupons as $c)
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ $c->code }}</span>
                    <form action="{{ route('coupons.destroy', $c->id) }}" method="POST">
                        @csrf @method("DELETE")
                        <button type="submit" class="btn btn-sm btn-link text-muted p-0" onclick="return confirm('Delete coupon?')"><i class="fas fa-times-circle fs-5"></i></button>
                    </form>
                </div>
                <h2 class="fw-bold mb-1">{{ (int)$c->discount_percentage }}% OFF</h2>
                <p class="text-muted small mb-4">Apply this code on POS screen to get discount.</p>
                <div class="mt-auto border-top pt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Min Order:</span>
                        <span class="fw-bold text-dark">${{ number_format($c->min_bill_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Expires:</span>
                        <span class="fw-bold text-danger">{{ $c->expiry_date ?? "Never" }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="modal fade" id="addCouponModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg px-2"><form action="{{ route('coupons.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">Create Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Coupon Code</label>
        <input type="text" name="code" class="form-control form-control-lg mb-3 shadow-none bg-light border-0" placeholder="e.g. PIZZA50" required>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Discount %</label>
        <div class="input-group mb-3">
            <input type="number" step="0.01" name="discount_percentage" class="form-control form-control-lg bg-light border-0 shadow-none text-center" placeholder="0" required>
            <span class="input-group-text bg-white border-0 fw-bold">% OFF</span>
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Minimum Bill Amount</label>
        <div class="input-group mb-3">
            <span class="input-group-text bg-white border-0 fw-bold">$</span>
            <input type="number" step="0.01" name="min_bill_amount" class="form-control form-control-lg bg-light border-0 shadow-none" placeholder="0.00">
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control form-control-lg shadow-none bg-light border-0">
    </div>
    <div class="modal-footer border-0 pb-4 pt-0">
        <button type="submit" class="btn btn-primary btn-lg w-100 py-2 rounded-3 fw-bold shadow-sm">INITIALIZE COUPON</button>
    </div>
</form></div></div></div>
@endsection
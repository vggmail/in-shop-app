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
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ $c->code }}</span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-link text-primary p-0" onclick="editCoupon({{ json_encode($c) }})"><i class="fas fa-edit fs-5"></i></button>
                        <form action="{{ route('coupons.destroy', $c->id) }}" method="POST">
                            @csrf @method("DELETE")
                            <button type="submit" class="btn btn-sm btn-link text-muted p-0" onclick="return confirm('Delete coupon?')"><i class="fas fa-times-circle fs-5"></i></button>
                        </form>
                    </div>
                </div>
                <div class="mb-2">
                    <span class="badge bg-soft-info text-info border-0 rounded-pill small">{{ $c->coupon_type }}</span>
                    @if($c->show_on_home && $c->coupon_type != 'Internal')
                        <span class="badge bg-soft-success text-success border-0 rounded-pill small"><i class="fas fa-eye me-1"></i> Public on Home</span>
                    @else
                        <span class="badge bg-soft-secondary text-muted border-0 rounded-pill small"><i class="fas fa-eye-slash me-1"></i> Hidden from Home</span>
                    @endif
                </div>
                <h2 class="fw-bold mb-1">{{ (int)$c->discount_percentage }}% OFF</h2>
                <p class="text-muted small mb-4">Apply this code on POS screen to get discount.</p>
                <div class="mt-auto border-top pt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Min Order:</span>
                        <span class="fw-bold text-dark">₹{{ number_format($c->min_bill_amount, 2) }}</span>
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

<!-- ADD MODAL -->
<div class="modal fade" id="addCouponModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg px-2"><form action="{{ route('coupons.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Create Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Coupon Code</label>
                <input type="text" name="code" class="form-control form-control-lg bg-light border-0 shadow-none" placeholder="e.g. PIZZA50" required>
            </div>
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Discount %</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="discount_percentage" class="form-control form-control-lg bg-light border-0 shadow-none" placeholder="0" required>
                    <span class="input-group-text bg-white border-0 fw-bold">%</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Coupon Type</label>
                <select name="coupon_type" class="form-select form-select-lg bg-light border-0 shadow-none">
                    <option value="Internal">Internal (Hidden)</option>
                    <option value="Customer">Customer</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Minimum Bill Amount (₹)</label>
                <input type="number" step="0.01" name="min_bill_amount" class="form-control form-control-lg bg-light border-0 shadow-none" placeholder="0.00">
            </div>
            <div class="col-md-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control form-control-lg bg-light border-0 shadow-none">
            </div>
            <div class="col-12 mt-3" id="add_home_box">
                <div class="form-check form-switch p-3 bg-light rounded d-flex justify-content-between align-items-center">
                    <label class="form-check-label fw-bold text-muted mb-0" for="show_on_home" id="add_home_label">Show on Home Page</label>
                    <input class="form-check-input ms-0 mt-0" type="checkbox" name="show_on_home" value="1" id="show_on_home" style="width: 2.5em; height: 1.25em;">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-4 fw-800 shadow-sm">CREATE COUPON</button></div>
</form></div></div></div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editCouponModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg px-2"><form id="editCouponForm" method="POST">
    @csrf @method("PUT")
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Edit Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Coupon Code</label>
                <input type="text" name="code" id="e_code" class="form-control form-control-lg bg-light border-0 shadow-none" required>
            </div>
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Discount %</label>
                <div class="input-group text-center">
                    <input type="number" step="0.01" name="discount_percentage" id="e_discount" class="form-control form-control-lg bg-light border-0 shadow-none" required>
                    <span class="input-group-text bg-white border-0 fw-bold">%</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Coupon Type</label>
                <select name="coupon_type" id="e_type" class="form-select form-select-lg bg-light border-0 shadow-none">
                    <option value="Internal">Internal (Hidden)</option>
                    <option value="Customer">Customer</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Minimum Bill Amount (₹)</label>
                <input type="number" step="0.01" name="min_bill_amount" id="e_min_bill" class="form-control form-control-lg bg-light border-0 shadow-none">
            </div>
            <div class="col-md-12">
                <label class="small fw-bold text-muted text-uppercase mb-1">Expiry Date</label>
                <input type="date" name="expiry_date" id="e_expiry" class="form-control form-control-lg bg-light border-0 shadow-none">
            </div>
            <div class="col-12 mt-3" id="edit_home_box">
                <div class="form-check form-switch p-3 bg-light rounded d-flex justify-content-between align-items-center">
                    <label class="form-check-label fw-bold text-muted mb-0" for="e_show_on_home" id="edit_home_label">Show on Home Page</label>
                    <input class="form-check-input ms-0 mt-0" type="checkbox" name="show_on_home" value="1" id="e_show_on_home" style="width: 2.5em; height: 1.25em;">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-4 fw-800 shadow-sm">UPDATE COUPON</button></div>
</form></div></div></div>
@endsection

@section("scripts")
<script>
function editCoupon(c) {
    $("#editCouponForm").attr("action", "/cp/coupons/" + c.id);
    $("#e_code").val(c.code);
    $("#e_discount").val(c.discount_percentage);
    $("#e_min_bill").val(c.min_bill_amount);
    $("#e_expiry").val(c.expiry_date);
    $("#e_type").val(c.coupon_type);
    $("#e_show_on_home").prop("checked", c.show_on_home == 1);
    
    // Disable home toggle based on type
    toggleHomeOption($("#e_type"), $("#e_show_on_home"));
    
    new bootstrap.Modal(document.getElementById("editCouponModal")).show();
}

function toggleHomeOption(select, toggle) {
    let box = toggle.closest(".col-12");
    let label = box.find("label");
    if(select.val() === "Internal") {
        toggle.prop("checked", false).prop("disabled", true);
        box.css({ "opacity": "0.5", "filter": "blur(0.5px)", "pointer-events": "none", "text-decoration": "line-through" });
        label.text("Show on Home (Disabled for Internal)");
    } else {
        toggle.prop("disabled", false);
        box.css({ "opacity": "1", "filter": "none", "pointer-events": "auto", "text-decoration": "none" });
        label.text("Show on Home Page");
    }
}

$(document).on("change", "#e_type", function() {
    toggleHomeOption($(this), $("#e_show_on_home"));
});

$(document).on("change", 'select[name="coupon_type"]', function() {
    let toggle = $(this).closest(".modal-body").find('input[name="show_on_home"]');
    toggleHomeOption($(this), toggle);
});
</script>
@endsection
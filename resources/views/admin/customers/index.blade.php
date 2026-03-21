@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <h2><i class="fas fa-users text-primary me-2"></i> CRM - Loyal Customers</h2>
</div>

<div class="row">
    @foreach($customers as $c)
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-user-tie fa-2x text-primary border border-2 border-primary rounded-circle p-2"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $c->name ?: "Guest Customer" }}</h5>
                    <p class="text-muted small mb-0"><i class="fas fa-id-badge me-1"></i> {{ $c->phone }}</p>
                </div>
                
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Orders</span>
                        <span class="fw-bold small">{{ $c->total_orders }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Total Spent</span>
                        <span class="fw-bold small text-success">${{ number_format($c->total_spending, 2) }}</span>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill py-1"><i class="fas fa-history small"></i> History</button>
                    <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger w-100 rounded-pill py-1"><i class="fas fa-trash small"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($customers->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No customers registered yet.</h5>
        <p class="small">Add customers directly via the POS screen during checkout.</p>
    </div>
    @endif
</div>
@endsection
@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-credit-card text-success me-2"></i> Payment Reconciliations</h2>
        <p class="text-muted small">Tracking all cash, card, and digital transactions.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 py-3 px-4 fw-bold">Recent Transactions</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Reference</th>
                            <th>Time</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">#ORD-{{ $p->order->id }}</td>
                            <td class="small">{{ date("H:i A", strtotime($p->created_at)) }}<br><span class="text-muted">{{ date("d M", strtotime($p->date)) }}</span></td>
                            <td>
                                @if($p->method == "Cash") <i class="fas fa-money-bill-wave text-success me-1"></i> 
                                @elseif($p->method == "Card") <i class="fas fa-credit-card text-primary me-1"></i> 
                                @else <i class="fas fa-mobile-alt text-info me-1"></i> @endif
                                {{ $p->method }}
                            </td>
                            <td class="text-end fw-bold text-success">${{ number_format($p->amount, 2) }}</td>
                            <td class="text-center align-middle">
                                <span class="badge border border-success text-success px-2 py-1 rounded-pill small" style="font-size: 10px;">{{ $p->status }}</span>
                            </td>
                            <td class="pe-4"><a href="{{ route('orders.show', $p->order_id) }}" class="btn btn-sm btn-light border small">View Details</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white p-4">
            <small class="fw-bold opacity-75">Today Cash Total</small>
            <h2 class="fw-bold mb-0">${{ number_format($payments->where('method', 'Cash')->where('date', date('Y-m-d'))->sum('amount'), 2) }}</h2>
        </div>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-info text-white p-4">
            <small class="fw-bold opacity-75">Today Digital/Card</small>
            <h2 class="fw-bold mb-0">${{ number_format($payments->whereIn('method', ['Card','UPI'])->where('date', date('Y-m-d'))->sum('amount'), 2) }}</h2>
        </div>
    </div>
</div>
@endsection
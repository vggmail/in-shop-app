@extends("layouts.admin")
@section("content")
<div class="mb-4 d-flex align-items-center justify-content-between">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item active">Order #{{ $payment->order->order_number }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0">Payment Details</h2>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-light border rounded-pill px-4">
        <i class="fas fa-arrow-left me-2"></i> Back to List
    </a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 fw-bold">Summary Info</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase d-block mb-1">Order Reference</label>
                    <a href="{{ route('orders.show', $payment->order_id) }}" class="fw-bold text-dark text-decoration-none">
                        #{{ $payment->order->order_number }} <i class="fas fa-external-link-alt small ms-1 opacity-50"></i>
                    </a>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase d-block mb-1">Customer</label>
                    <span class="fw-bold">{{ $payment->order->customer->name ?? 'Guest' }}</span><br>
                    <small class="text-muted">{{ $payment->order->customer->phone ?? '' }}</small>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase d-block mb-1">Final Amount</label>
                    <h3 class="fw-bold text-success mb-0">₹{{ number_format($payment->amount, 2) }}</h3>
                </div>
                <div class="mb-0">
                    <label class="small text-muted text-uppercase d-block mb-1">Status</label>
                    <span class="badge bg-success rounded-pill px-3">{{ $payment->status }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h5 class="fw-bold mb-3"><i class="fas fa-history text-info me-2"></i> Transaction Attempts ({{ count($attempts) }})</h5>
        
        @if(count($attempts) > 0)
            @foreach($attempts as $index => $at)
            <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-4">
                    <div>
                        <span class="badge bg-{{ $at->status == 'Success' ? 'success' : ($at->status == 'Initiated' ? 'info' : 'danger') }} me-2">
                            {{ $at->status }}
                        </span>
                        <small class="fw-bold text-muted">Attempted at: {{ $at->created_at->format('d M Y, h:i A') }}</small>
                    </div>
                    <small class="text-muted">TXN: {{ $at->txnid }}</small>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted text-uppercase d-block mb-1">Amount</label>
                            <span class="fw-bold">₹{{ number_format($at->amount, 2) }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted text-uppercase d-block mb-1">PayU ID (MIH)</label>
                            <span class="text-{{ $at->mihpayid ? 'dark fw-bold' : 'muted italic' }}">{{ $at->mihpayid ?? 'N/A' }}</span>
                        </div>
                        
                        @if($at->error_message)
                        <div class="col-12 mb-3">
                            <label class="small text-muted text-uppercase d-block mb-1">Error Message</label>
                            <div class="alert alert-danger mb-0 py-2 small border-0">{{ $at->error_message }}</div>
                        </div>
                        @endif

                        <div class="col-12">
                            <button class="btn btn-sm btn-link text-info p-0 fw-bold text-decoration-none" data-bs-toggle="collapse" data-bs-target="#debug-{{ $at->id }}">
                                <i class="fas fa-bug me-1"></i> Debug JSON Data
                            </button>
                            <div class="collapse mt-3" id="debug-{{ $at->id }}">
                                <div class="row">
                                    @if($at->hash_string)
                                    <div class="col-12 mb-2">
                                        <label class="small text-muted fw-bold">Outgoing Hash String:</label>
                                        <code class="d-block bg-light p-2 rounded small text-wrap" style="word-break: break-all;">{{ $at->hash_string }}</code>
                                    </div>
                                    @endif
                                    <div class="col-md-6 mb-2">
                                        <label class="small text-muted fw-bold d-block">Request Data:</label>
                                        <pre class="bg-dark text-white p-2 rounded small overflow-auto" style="max-height: 200px;">{{ json_encode($at->request_data, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small text-muted fw-bold d-block">Response Data:</label>
                                        <pre class="bg-dark text-white p-2 rounded small overflow-auto" style="max-height: 200px;">{{ json_encode($at->response_data, JSON_PRETTY_PRINT) ?? 'No response received' }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="alert alert-info rounded-4 shadow-sm border-0">
                <i class="fas fa-info-circle me-2"></i> No detailed attempts recorded for this payment yet.
            </div>
        @endif
    </div>
</div>
@endsection

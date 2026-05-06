@extends("layouts.admin")
@section("content")
    <div class="d-flex flex-column flex-md-row justify-content-between mb-3 align-items-md-center gap-3 gap-md-0">
        <h2>Order Queue</h2>
        <form action="{{ route('orders.index') }}" method="GET" class="d-flex w-100" style="max-width: 400px;">
            <input type="text" name="customer_search" class="form-control me-2 rounded-pill px-3 shadow-sm border-0"
                placeholder="Search order, mobile or name..." value="{{ request('customer_search') }}">
            <button type="submit" class="btn btn-primary rounded-circle shadow-sm flex-shrink-0"
                style="width: 40px; height: 40px; border-radius: 50% !important; display: flex; align-items: center; justify-content: center;"><i
                    class="fas fa-search"></i></button>
            @if(request('customer_search'))
                <a href="{{ route('orders.index') }}" class="btn btn-danger ms-2 rounded-circle shadow-sm flex-shrink-0"
                    style="width: 40px; height: 40px; border-radius: 50% !important; display: flex; align-items: center; justify-content: center;"
                    title="Clear Search"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
    <div class="card bg-white p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="min-width: 800px;">
                <thead class="table-light">
                    <tr>
                        <th>Order / Customer</th>
                        <th>Type/Table</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr>
                            <td>
                                <div class="fw-bold text-primary" style="font-size: 14px;">{{ $o->order_number }}</div>
                                @if($o->customer)
                                    <div class="small fw-bold text-dark mt-1">{{ Str::limit($o->customer->name, 15) }}</div>
                                    <div class="small text-muted" style="font-size: 10px;"><i class="fas fa-mobile-alt"></i>
                                        {{ $o->customer->phone }}</div>
                                @else
                                    <div class="small text-muted mt-1" style="font-size: 11px;"><i class="fas fa-user-secret"></i>
                                        Guest</div>
                                @endif
                            </td>
                            <td>
                                @if($o->order_type == "Takeaway")
                                    <span class="badge bg-secondary"><i class="fas fa-shopping-bag"></i> Takeaway</span>
                                @elseif($o->order_type == "Home Delivery")
                                    <span class="badge bg-info text-dark"><i class="fas fa-motorcycle"></i> Home Delivery</span>
                                    @if($o->delivery_address)
                                        <div class="small text-muted" style="font-size: 11px;" title="{{ $o->delivery_address }}">
                                            {{ Str::limit($o->delivery_address, 20) }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-primary"><i class="fas fa-chair"></i> Dine-In</span>
                                    <div class="small fw-bold text-muted">{{ $o->table_number }}</div>
                                @endif
                            </td>
                            <td class="font-weight-bold text-success">&#8377;{{ $o->grand_total }}</td>
                            <td>
                                <form action="{{ route('orders.updateStatus', $o->id) }}" method="POST">
                                    @csrf
                                    <select name="status"
                                        class="form-select form-select-sm fw-bold {{ $o->status == 'Preparing' ? 'bg-warning text-dark' : ($o->status == 'Ready' ? 'bg-info text-white' : ($o->status == 'Cancelled' ? 'bg-danger text-white' : 'bg-success text-white')) }}"
                                        onchange="this.form.submit()">
                                        <option value="Preparing" {{ $o->status == 'Preparing' ? 'selected' : '' }}>Preparing
                                        </option>
                                        <option value="Ready" {{ $o->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                                        <option value="Completed" {{ $o->status == 'Completed' ? 'selected' : '' }}>Completed
                                        </option>
                                        <option value="Cancelled" {{ $o->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('orders.updateStatus', $o->id) }}" method="POST">
                                    @csrf
                                    <select name="payment_status"
                                        class="form-select form-select-sm fw-bold mt-1 {{ $o->payment_status == 'Pending' ? 'bg-danger text-white' : ($o->payment_status == 'Refunded' ? 'bg-warning text-dark' : 'bg-success text-white') }}"
                                        onchange="this.form.submit()">
                                        <option value="Pending" {{ $o->payment_status == 'Pending' ? 'selected' : '' }}>
                                            {{ $o->payment_method }} - Pending</option>
                                        <option value="Paid" {{ $o->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="Refunded" {{ $o->payment_status == 'Refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                </form>
                            </td>
                            <td class="small">{{ $o->created_at->diffForHumans() }}<br><span
                                    class="text-muted">{{ $o->created_at->format("H:i A") }}</span></td>
                            <td>
                                <a href="{{ route('orders.show', $o->id) }}" class="btn btn-sm btn-outline-dark"><i
                                        class="fas fa-eye"></i> View</a>
                                <a target="_blank" href="{{ route('orders.invoice', $o->id) }}"
                                    class="btn btn-sm btn-outline-info"><i class="fas fa-print"></i> Receipt</a>
                                @if(!in_array($o->status, ['Cancelled','Completed']))
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelModal{{ $o->id }}">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $orders->links("pagination::bootstrap-5") }}
        </div>
    </div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endif
@if(session('warning'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-warning border-0 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}</div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endif
@if(session('error'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-times-circle me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endif

{{-- Per-order Cancel Modals --}}
@foreach($orders as $o)
    @if(!in_array($o->status, ['Cancelled','Completed']))
    <div class="modal fade" id="cancelModal{{ $o->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Cancel {{ $o->order_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Cancel this order for
                        <strong>{{ $o->customer->name ?? 'Guest' }}</strong>
                        worth <strong>₹{{ number_format($o->grand_total, 2) }}</strong>?
                    </p>
                    @php $op = $o->payments()->where('method','PayU')->where('status','Paid')->first(); @endphp
                    @if($op)
                        <div class="alert alert-info border-0 mb-0 small">
                            <i class="fas fa-undo-alt me-1"></i>
                            Paid via <strong>PayU</strong> — refund of <strong>₹{{ number_format($op->amount, 2) }}</strong> will be auto-initiated.
                        </div>
                    @else
                        <div class="alert alert-warning border-0 mb-0 small">
                            <i class="fas fa-info-circle me-1"></i>
                            No online payment found. Only order status will be set to <strong>Cancelled</strong>.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                    <form action="{{ route('orders.cancelOrder', $o->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban me-1"></i>
                            {{ isset($op) && $op ? 'Cancel & Refund' : 'Cancel Order' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
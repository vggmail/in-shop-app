@foreach($orders as $order)
    <div class="card order-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="text-muted small mb-1">ORDER #{{ $order->order_number }}</h6>
                    <p class="text-muted small mb-0">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <span class="status-badge {{ $order->status == 'Completed' ? 'bg-success text-white' : ($order->status == 'Cancelled' ? 'bg-danger text-white' : 'bg-warning text-dark') }}">
                    {{ strtoupper($order->status) }}
                </span>
            </div>

            <hr class="my-3 opacity-50">

            <div class="mb-3">
                @foreach($order->items as $item)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $item->quantity }}x {{ $item->item->name }} @if($item->variant) ({{ $item->variant->name }}) @endif</span>
                        <span>&#8377;{{ number_format($item->total, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted small">Total Paid:</span>
                    <h5 class="fw-bold mb-0">&#8377;{{ number_format($order->grand_total, 2) }}</h5>
                </div>
                <div>
                    <a href="{{ url('/order/'.$order->order_number.'/success') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Details
                    </a>
                    <a href="{{ route('customer.reorder', $order->order_number) }}" class="btn btn-sm btn-dark rounded-pill px-3 ms-2">
                        <i class="fas fa-redo me-1"></i> Repeat
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach

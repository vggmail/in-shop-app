@foreach($orders as $order)
    <div class="card order-card" data-order-url="{{ url('/order/'.$order->order_number.'/success') }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="text-muted mb-0" style="font-size: 10px; font-weight: 800;">ORDER #{{ $order->order_number }}</h6>
                    <p class="text-muted mb-0" style="font-size: 10px;">{{ $order->created_at->format('d M, h:i A') }}</p>
                </div>
                <div class="text-end">
                    <span class="status-badge {{ $order->status == 'Completed' ? 'bg-success text-white' : ($order->status == 'Cancelled' ? 'bg-danger text-white' : 'bg-warning text-dark') }}">
                        {{ $order->status }}
                    </span>
                </div>
            </div>

            <div class="mb-2">
                @if($order->payment_status == 'Paid')
                    <span class="payment-badge bg-success-subtle text-success border border-success-subtle">
                        <i class="fas fa-check-circle me-1"></i> Paid Via {{ $order->payment_method ?? 'Online' }}
                    </span>
                @else
                    <span class="payment-badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                        <i class="fas fa-clock me-1"></i> Payment {{ $order->payment_status ?? 'Pending' }}
                    </span>
                @endif
            </div>

            <div class="mb-3 py-2 border-top border-bottom border-light">
                @php $itemCount = $order->items->count(); @endphp
                @foreach($order->items->take(2) as $item)
                    <div class="d-flex justify-content-between x-small mb-1" style="font-size: 11px;">
                        <span class="text-truncate me-2">{{ $item->quantity }}x {{ $item->item->name ?? 'Deleted Item' }} {{ $item->variant ? '- ' . $item->variant->name : ($item->item && $item->item->default_size ? '- ' . $item->item->default_size : '') }}</span>
                        <span class="fw-bold">&#8377;{{ number_format($item->total, 0) }}</span>
                    </div>
                @endforeach
                @if($itemCount > 2)
                    <div class="text-muted text-center" style="font-size: 9px;">+ {{ $itemCount - 2 }} more items</div>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-800 mb-0" style="color: var(--primary)">&#8377;{{ number_format($order->grand_total, 2) }}</h5>
                </div>
                <div class="btn-group">
                    <a href="{{ url('/order/'.$order->order_number.'/success') }}" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1" style="font-size: 10px; font-weight: 700;">
                        Details
                    </a>
                    <a href="{{ route('customer.reorder', $order->order_number) }}" class="btn btn-xs btn-dark rounded-pill px-2 py-1 ms-1" style="font-size: 10px; font-weight: 700;">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach

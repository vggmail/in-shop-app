@extends('layouts.kds')

@section('styles')
<style>
    .cds-topbar { background: #1e1b4b; border-bottom: 1px solid #312e81; }
    .badge-ready { background: #14532d; color: #4ade80; animation: pulse-green 1.5s infinite; }
    @keyframes pulse-green { 0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); } 100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); } }
    .card-ready { border-color: #22c55e !important; background: #064e3b !important; }
    .btn-pay { background: #f59e0b; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: bold; flex: 1; }
    .btn-complete { background: #22c55e; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: bold; flex: 1; }
    .payment-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px; }
    .payment-paid { background: #065f46; color: #34d399; }
    .payment-pending { background: #7f1d1d; color: #f87171; }
</style>
@endsection

@section('content')
<div class="kds-filters">
    <button class="kds-filter-btn active" onclick="filterCDS('all', this)">All active</button>
    <button class="kds-filter-btn" onclick="filterCDS('Ready', this)">Ready to Handover</button>
    <button class="kds-filter-btn" onclick="filterCDS('Preparing', this)">Preparing</button>
</div>

<div class="kds-board" id="cds-board">
    @forelse($orders as $order)
        @php
            $isReady = $order->status === 'Ready';
            $isPaid = $order->payment_status === 'Paid';
        @endphp
        <div class="kds-card {{ $isReady ? 'card-ready' : '' }}" 
             id="cds-card-{{ $order->id }}" 
             data-status="{{ $order->status }}"
             data-payment="{{ $order->payment_status }}">
            
            <div class="kds-card-header">
                <div>
                    <div class="order-number" style="font-size: 1.5rem;">
                        Token #{{ $order->token_number }}
                    </div>
                    <div class="small text-muted">ORD #{{ $order->order_number }}</div>
                </div>
                <div class="text-end">
                    <span class="order-type-badge {{ $isReady ? 'badge-ready' : 'badge-dinein' }}">
                        {{ $order->status }}
                    </span>
                    <br>
                    <span class="payment-badge {{ $isPaid ? 'payment-paid' : 'payment-pending' }}">
                        {{ $isPaid ? 'PAID' : 'UNPAID' }} ({{ $order->payment_method }})
                    </span>
                </div>
            </div>

            <div class="kds-items" style="min-height: 80px;">
                <div class="fw-bold text-white">{{ $order->customer?->name ?? 'Guest' }}</div>
                <div class="text-muted small">{{ $order->items_count }} Items | ₹{{ number_format($order->grand_total, 2) }}</div>
                @if($order->table_number)
                    <div class="mt-2 text-info fw-bold">Table: {{ $order->table_number }}</div>
                @endif
            </div>

            <div class="kds-card-footer gap-2">
                @if(!$isPaid)
                    <button class="btn-pay" onclick="updateCDS({{ $order->id }}, {payment_status: 'Paid'}, this)">
                        <i class="fas fa-money-bill me-1"></i> Paid
                    </button>
                @endif
                <button class="btn-complete" onclick="updateCDS({{ $order->id }}, {status: 'Completed'}, this)">
                    <i class="fas fa-check-double me-1"></i> Handover
                </button>
            </div>
        </div>
    @empty
        <div class="kds-empty" id="cds-empty-state" style="grid-column: 1/-1;">
            <div class="empty-icon">🛋️</div>
            <h3>Queue is empty!</h3>
            <p>Ready orders will appear here for handover.</p>
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    function updateCDS(orderId, data, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`/cp/cds/${orderId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (data.status === 'Completed') {
                    document.getElementById(`cds-card-${orderId}`).remove();
                    checkEmpty();
                } else {
                    // Update payment UI locally
                    const card = document.getElementById(`cds-card-${orderId}`);
                    card.setAttribute('data-payment', 'Paid');
                    const badge = card.querySelector('.payment-badge');
                    badge.className = 'payment-badge payment-paid';
                    badge.textContent = 'PAID (' + res.payment_method + ')';
                    btn.remove(); // Remove "Paid" button
                }
            }
        });
    }

    function checkEmpty() {
        if (document.querySelectorAll('.kds-card').length === 0) {
            location.reload();
        }
    }

    function filterCDS(filter, btn) {
        document.querySelectorAll('.kds-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.kds-card').forEach(card => {
            if (filter === 'all') card.style.display = 'block';
            else card.style.display = card.getAttribute('data-status') === filter ? 'block' : 'none';
        });
    }

    // Auto-poll for CDS
    function pollCDS() {
        fetch('/cp/cds/poll')
        .then(r => r.json())
        .then(data => {
            // Very basic sync - if new orders found, reload or append
            // For now, let's just refresh if counts differ for simplicity in this demo
            // or we can implement full DOM diffing if needed.
            const currentCount = document.querySelectorAll('.kds-card').length;
            if (data.orders.length > currentCount) {
                location.reload(); 
            }
        });
    }
    setInterval(pollCDS, 5000);
</script>
@endsection

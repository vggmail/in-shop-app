@extends('layouts.kds')

@section('content')

{{-- Filter Tabs --}}
<div class="kds-filters">
    <button class="kds-filter-btn active" onclick="filterKDS('all', this)">
        <i class="fas fa-th-large me-1"></i> All Active
    </button>
    <button class="kds-filter-btn" onclick="filterKDS('Preparing', this)">
        <i class="fas fa-fire me-1"></i> Preparing
    </button>
    <button class="kds-filter-btn" onclick="filterKDS('Ready', this)">
        <i class="fas fa-check me-1"></i> Ready
    </button>
    <button class="kds-filter-btn" onclick="filterKDS('Dine-In', this)" data-type="Dine-In">
        <i class="fas fa-chair me-1"></i> Dine-In
    </button>
    <button class="kds-filter-btn" onclick="filterKDS('Takeaway', this)" data-type="Takeaway">
        <i class="fas fa-shopping-bag me-1"></i> Takeaway
    </button>
    <button class="kds-filter-btn" onclick="filterKDS('Delivery', this)" data-type="Delivery">
        <i class="fas fa-motorcycle me-1"></i> Delivery
    </button>
</div>

{{-- KDS Board --}}
<div class="kds-board" id="kds-board">
    @forelse($orders as $order)
    @php
        $ageMinutes = $order->created_at->diffInMinutes(now());
        $urgency = $ageMinutes >= 20 ? 'urgent' : ($ageMinutes >= 10 ? 'warning' : '');
        $timerClass = $ageMinutes >= 20 ? 'timer-urgent' : ($ageMinutes >= 10 ? 'timer-warn' : 'timer-ok');
        $typeSlug = strtolower(str_replace(' ', '', $order->order_type));
        $badgeClass = $typeSlug === 'homedelivery' ? 'badge-delivery'
                    : ($typeSlug === 'takeaway'     ? 'badge-takeaway'
                    : ($order->source === 'Online'  ? 'badge-online' : 'badge-dinein'));
        $isReady = $order->status === 'Ready';
    @endphp
    <div class="kds-card {{ $urgency }} {{ $isReady ? 'ready' : '' }}"
         id="kds-card-{{ $order->id }}"
         data-status="{{ $order->status }}"
         data-type="{{ $order->order_type }}"
         data-created="{{ $order->created_at->timestamp }}">

        {{-- Header --}}
        <div class="kds-card-header">
            <div>
                <div class="order-number">
                    <span class="token-badge {{ !$order->token_number ? 'token-na' : '' }}">Token #{{ $order->token_number ?? 'N/A' }}</span>
                    <div style="font-size:0.7rem; color:#94a3b8; font-weight:500; margin-top:2px;">
                        ORD #{{ $order->order_number }}
                        @if($order->table_number)
                             · Table {{ $order->table_number }}
                        @endif
                    </div>
                </div>
                <div class="wait-timer {{ $timerClass }}">
                    <i class="fas fa-clock"></i>
                    <span class="order-timer" data-created="{{ $order->created_at->timestamp }}">{{ $ageMinutes }}m ago</span>
                </div>
                @if($order->customer)
                    <div style="font-size:0.7rem; color:#64748b; margin-top:3px;">
                        <i class="fas fa-user me-1"></i>{{ $order->customer->name }}
                    </div>
                @endif
            </div>
            <div class="text-end">
                <span class="order-type-badge {{ $badgeClass }}">
                    {{ $order->order_type === 'Home Delivery' ? 'Delivery' : $order->order_type }}
                    @if($order->source === 'Online') · Online @endif
                </span>
                @if($isReady)
                    <div style="font-size:0.7rem; color:#22c55e; font-weight:700; margin-top:4px;">
                        <i class="fas fa-check-circle me-1"></i>READY
                    </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="kds-items">
            @foreach($order->items as $item)
            <div class="kds-item">
                <div class="item-qty">{{ $item->quantity }}</div>
                <div class="item-info">
                    <div class="item-name">
                        {{ $item->item ? $item->item->name : 'Item #' . $item->item_id }}
                        @if($item->item && $item->item->default_size && !$item->variant)
                            <span style="font-weight:500; color:#94a3b8;"> · {{ $item->item->default_size }}</span>
                        @endif
                    </div>
                    @if($item->variant)
                        <div class="item-variant"><i class="fas fa-tag me-1"></i>{{ $item->variant->name }}</div>
                    @endif
                    @if($item->extras->count() > 0)
                        <div class="item-extras">
                            <i class="fas fa-plus-circle me-1"></i>
                            {{ $item->extras->map(fn($e) => $e->extra->name ?? 'Extra')->join(', ') }}
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Special Note --}}
        @if($order->note)
        <div class="kds-note">
            <i class="fas fa-sticky-note me-2" style="margin-top:1px; flex-shrink:0;"></i>
            <span>{{ $order->note }}</span>
        </div>
        @endif

        {{-- Footer Actions --}}
        <div class="kds-card-footer">
            @if(!$isReady)
                <button class="btn-kds-ready" onclick="markKDSStatus({{ $order->id }}, 'Ready', this)">
                    <i class="fas fa-bell"></i> Mark Ready
                </button>
            @else
                <button class="btn-kds-ready" style="background: linear-gradient(135deg,#6366f1,#4f46e5);"
                        onclick="markKDSStatus({{ $order->id }}, 'Completed', this)">
                    <i class="fas fa-check-double"></i> Mark Completed
                </button>
            @endif
            <button class="btn-kds-done" onclick="dismissCard({{ $order->id }})" title="Dismiss">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="kds-empty" id="kds-empty-state" style="grid-column: 1/-1;">
        <div class="empty-icon">🍽️</div>
        <h3>Kitchen is clear!</h3>
        <p>No active orders right now. New orders will appear automatically.</p>
    </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentFilter = 'all';
    let knownOrderIds = new Set([{{ $orders->pluck('id')->join(',') }}]);
    let preparingCount = {{ $orders->where('status','Preparing')->count() }};
    let readyCount     = {{ $orders->where('status','Ready')->count() }};

    // Update stat counters
    function updateCounters() {
        document.getElementById('kds-preparing-count').textContent = preparingCount;
        document.getElementById('kds-ready-count').textContent     = readyCount;
    }
    updateCounters();

    // Mark order status via AJAX
    function markKDSStatus(orderId, newStatus, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        fetch(`/cp/kds/${orderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`kds-card-${orderId}`);
                if (newStatus === 'Ready') {
                    card.classList.remove('urgent', 'warning');
                    card.classList.add('ready');
                    card.setAttribute('data-status', 'Ready');
                    card.querySelector('.kds-card-footer').innerHTML = `
                        <button class="btn-kds-ready" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"
                                onclick="markKDSStatus(${orderId}, 'Completed', this)">
                            <i class="fas fa-check-double"></i> Mark Completed
                        </button>
                        <button class="btn-kds-done" onclick="dismissCard(${orderId})" title="Dismiss">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preparingCount = Math.max(0, preparingCount - 1);
                    readyCount++;
                    // Play ready sound
                    playChime('ready');
                } else if (newStatus === 'Completed') {
                    dismissCard(orderId);
                    readyCount = Math.max(0, readyCount - 1);
                    playChime('done');
                }
                updateCounters();
                applyFilter(currentFilter);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed. Retry';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed. Retry';
        });
    }

    // Dismiss (remove) card from view
    function dismissCard(orderId) {
        const card = document.getElementById(`kds-card-${orderId}`);
        if (card) {
            card.style.transition = 'opacity 0.3s, transform 0.3s';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                card.remove();
                knownOrderIds.delete(orderId);
                checkEmptyState();
            }, 320);
        }
    }

    // Show empty state if no cards
    function checkEmptyState() {
        const board = document.getElementById('kds-board');
        const cards = board.querySelectorAll('.kds-card');
        const empty = document.getElementById('kds-empty-state');
        if (cards.length === 0 && !empty) {
            board.innerHTML = `
                <div class="kds-empty" id="kds-empty-state" style="grid-column:1/-1;">
                    <div class="empty-icon">🍽️</div>
                    <h3>Kitchen is clear!</h3>
                    <p>No active orders right now. New orders will appear automatically.</p>
                </div>`;
        }
    }

    // Filter orders
    function filterKDS(filter, btn) {
        currentFilter = filter;
        document.querySelectorAll('.kds-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        applyFilter(filter);
    }

    function applyFilter(filter) {
        document.querySelectorAll('.kds-card').forEach(card => {
            const status = card.getAttribute('data-status');
            const type   = card.getAttribute('data-type') || '';
            let show = true;
            if (filter === 'Preparing')  show = status === 'Preparing';
            else if (filter === 'Ready') show = status === 'Ready';
            else if (filter === 'Dine-In')  show = type === 'Dine-In';
            else if (filter === 'Takeaway') show = type === 'Takeaway';
            else if (filter === 'Delivery') show = type === 'Home Delivery';
            card.style.display = show ? '' : 'none';
        });
    }

    // Auto-refresh: poll for new orders every 8 seconds
    function pollNewOrders() {
        fetch(`/cp/kds/poll`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            preparingCount = data.preparing_count;
            readyCount     = data.ready_count;
            updateCounters();

            // Inject new order cards
            data.orders.forEach(order => {
                if (!knownOrderIds.has(order.id)) {
                    knownOrderIds.add(order.id);
                    // Remove empty state if present
                    const empty = document.getElementById('kds-empty-state');
                    if (empty) empty.remove();

                    const board = document.getElementById('kds-board');
                    board.insertAdjacentHTML('afterbegin', renderOrderCard(order));
                    preparingCount++;
                    playChime('new');
                }
            });

            // Update timer urgency on all cards
            updateTimers();
        })
        .catch(() => {});
    }
    setInterval(pollNewOrders, 8000);

    // Live wait timers
    function updateTimers() {
        document.querySelectorAll('.order-timer').forEach(el => {
            const created = parseInt(el.getAttribute('data-created'));
            const mins = Math.floor((Date.now() / 1000 - created) / 60);
            el.textContent = mins + 'm ago';

            const card   = el.closest('.kds-card');
            const timerDiv = el.parentElement;
            timerDiv.className = 'wait-timer ' + (mins >= 20 ? 'timer-urgent' : mins >= 10 ? 'timer-warn' : 'timer-ok');
            if (card.getAttribute('data-status') !== 'Ready') {
                card.classList.remove('urgent', 'warning');
                if (mins >= 20) card.classList.add('urgent');
                else if (mins >= 10) card.classList.add('warning');
            }
        });
    }
    setInterval(updateTimers, 30000);
    updateTimers();

    // Render a new order card HTML from poll data
    function renderOrderCard(o) {
        const ageM = Math.floor((Date.now() / 1000 - o.created_at_ts) / 60);
        const urgency = ageM >= 20 ? 'urgent' : (ageM >= 10 ? 'warning' : '');
        const timerCls = ageM >= 20 ? 'timer-urgent' : (ageM >= 10 ? 'timer-warn' : 'timer-ok');
        const typeSlug = (o.order_type || '').toLowerCase().replace(/\s/g,'');
        const badgeCls = typeSlug === 'homedelivery' ? 'badge-delivery'
                       : (typeSlug === 'takeaway' ? 'badge-takeaway'
                       : (o.source === 'Online' ? 'badge-online' : 'badge-dinein'));

        let itemsHtml = (o.items || []).map(item => `
            <div class="kds-item">
                <div class="item-qty">${item.quantity}</div>
                <div class="item-info">
                    <div class="item-name">${item.name}${item.variant ? ' · <span style="color:#94a3b8;">' + item.variant + '</span>' : ''}</div>
                    ${item.extras ? '<div class="item-extras"><i class="fas fa-plus-circle me-1"></i>' + item.extras + '</div>' : ''}
                </div>
            </div>`).join('');

        let noteHtml = o.note ? `<div class="kds-note"><i class="fas fa-sticky-note me-2"></i>${o.note}</div>` : '';

        return `
        <div class="kds-card ${urgency} new-order" id="kds-card-${o.id}"
             data-status="Preparing" data-type="${o.order_type}"
             data-created="${o.created_at_ts}">
            <div class="kds-card-header">
                <div>
                    <div class="order-number">
                        <span class="token-badge ${!o.token_number ? 'token-na' : ''}">Token #${o.token_number || 'N/A'}</span>
                        <div style="font-size:0.7rem; color:#94a3b8; font-weight:500; margin-top:2px;">
                            ORD #${o.order_number}
                            ${o.table_number ? ' · Table ' + o.table_number : ''}
                        </div>
                    </div>
                    <div class="wait-timer ${timerCls}">
                        <i class="fas fa-clock"></i>
                        <span class="order-timer" data-created="${o.created_at_ts}">${ageM}m ago</span>
                    </div>
                    ${o.customer ? '<div style="font-size:0.7rem;color:#64748b;margin-top:3px;"><i class="fas fa-user me-1"></i>' + o.customer + '</div>' : ''}
                </div>
                <div class="text-end">
                    <span class="order-type-badge ${badgeCls}">${o.order_type === 'Home Delivery' ? 'Delivery' : o.order_type}${o.source === 'Online' ? ' · Online' : ''}</span>
                </div>
            </div>
            <div class="kds-items">${itemsHtml}</div>
            ${noteHtml}
            <div class="kds-card-footer">
                <button class="btn-kds-ready" onclick="markKDSStatus(${o.id}, 'Ready', this)">
                    <i class="fas fa-bell"></i> Mark Ready
                </button>
                <button class="btn-kds-done" onclick="dismissCard(${o.id})" title="Dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
    }

    // Chime sounds
    function playChime(type) {
        const urls = {
            new:   'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
            ready: 'https://assets.mixkit.co/active_storage/sfx/2867/2867-preview.mp3',
            done:  'https://assets.mixkit.co/active_storage/sfx/2863/2863-preview.mp3'
        };
        new Audio(urls[type] || urls.new).play().catch(() => {});
    }
</script>
@endsection

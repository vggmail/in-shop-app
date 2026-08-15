@extends("layouts.admin")
@section('page_title', 'Counter Display')
@section('header_title', 'Counter Display')

@section("styles")
<style>
    /* Table View Styles */
    .table-view-container { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
    
    /* Sub Header */
    .table-subheader {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-view-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; }
    .delivery-btns .btn { border-radius: 6px; font-weight: 600; font-size: 0.85rem; padding: 6px 20px; border: none; margin-left: 10px; color: white; background: var(--accent-color); transition: 0.2s; }
    .delivery-btns .btn:hover { background: #e03d4b; transform: translateY(-1px); }
    
    /* Counter Clock */
    .counter-clock {
        color: #1e293b;
        padding: 5px 15px;
        font-family: 'Inter', sans-serif;
        font-weight: 800;
        font-size: 1.4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 1;
    }
    .clock-time { letter-spacing: 1px; }
    
    /* Filters Bar */
    .table-filters {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .filter-btn { background: var(--accent-color); color: white; border-radius: 6px; font-weight: 600; padding: 6px 14px; border: none; font-size: 0.8rem; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
    .filter-btn:hover { background: #e03d4b; color: white; }
    
    .status-legend { display: flex; gap: 15px; align-items: center; font-size: 0.75rem; font-weight: 600; color: #64748b; }
    .status-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .status-toggle { background: #e2e8f0; color: #64748b; padding: 4px 12px; border-radius: 15px; border: none; font-weight: 600; font-size: 0.75rem; display: flex; align-items: center; gap: 6px; }
    
    /* Floor Plan */
    .floor-plan-select { font-size: 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px; background: white; font-weight: 600; color: #334155; outline: none; }
    
    /* Table Grid */
    .table-section { padding: 20px; }
    .section-title { font-weight: 700; color: #1e293b; margin-bottom: 15px; font-size: 1.05rem; display: flex; align-items: center; }
    .section-title::before { content: ""; display: inline-block; width: 4px; height: 18px; background: var(--accent-color); border-radius: 4px; margin-right: 8px; }
    
    .table-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
    }
    .table-box {
        border-radius: 12px;
        height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        position: relative;
        color: #334155;
        border: 2px dashed #cbd5e1;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
        text-decoration: none;
    }
    .table-box:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #94a3b8; color: #1e293b; }
    
    /* Status Colors */
    .table-box.running { background: #bae6fd; border: 2px solid #7dd3fc; border-style: solid; }
    .table-box.printed { background: #bbf7d0; border: 2px solid #86efac; border-style: solid; }
    .table-box.paid { background: #fef08a; border: 2px solid #fde047; border-style: solid; }
    .table-box.kot { background: #fef08a; border: 2px solid #fde047; border-style: solid; }
    
    .table-actions {
        position: absolute;
        bottom: 8px;
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.9);
        padding: 4px 8px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        opacity: 0;
        transition: opacity 0.2s;
        backdrop-filter: blur(4px);
    }
    .table-box:hover .table-actions { opacity: 1; }
    .table-actions i { font-size: 0.8rem; color: #475569; cursor: pointer; transition: 0.2s; }
    .table-actions i:hover { color: var(--accent-color); transform: scale(1.1); }
    .table-actions-always { opacity: 1; }
    
    .table-time {
        font-size: 0.65rem;
        color: #64748b;
        margin-top: 4px;
        font-weight: 500;
    }
    .table-amount {
        font-size: 0.75rem;
        color: #ef4444;
        font-weight: 800;
        margin-top: 2px;
    }
    .table-token {
        font-size: 0.65rem;
        font-weight: 700;
        margin-top: 4px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
    }
    .token-na {
        background: #fee2e2;
        color: #b91c1c;
    }
</style>
@endsection

@section("content")

<div class="table-view-container mb-4">
    <!-- Sub Header -->
    <div class="table-subheader">
        <div class="table-view-title"><i class="fas fa-th-large text-danger me-2"></i>Counter Display</div>
        
        <div class="counter-clock mx-auto d-none d-md-flex">
            <div class="clock-time" id="digitalClock">00:00:00</div>
        </div>

        <div class="delivery-btns d-flex align-items-center">
            <a href="javascript:void(0)" onclick="location.reload()" class="text-muted me-3 text-decoration-none transition" title="Refresh"><i class="fas fa-sync-alt"></i></a>
            <a href="javascript:void(0)" onclick="toggleFullScreen()" class="text-muted me-3 text-decoration-none transition" id="fullscreenBtn" title="Fullscreen"><i class="fas fa-expand fa-lg"></i></a>
            <a href="{{ route('pos.index') }}?type=Delivery" class="btn"><i class="fas fa-motorcycle me-1"></i> Delivery</a>
            <a href="{{ route('pos.index') }}?type=Takeaway" class="btn"><i class="fas fa-walking me-1"></i> Take Away</a>
        </div>
    </div>

    @php
        $floorPlans = app('tenant')->floor_plans ?: [
            ['name' => 'Main Hall (A/C)', 'start' => 1, 'end' => 15],
            ['name' => 'Outdoor (Non A/C)', 'start' => 16, 'end' => 25],
            ['name' => 'Bar', 'start' => 26, 'end' => 30]
        ];
    @endphp

    <!-- Filters -->
    <div class="table-filters">
        <div class="d-flex gap-2">
            <button class="filter-btn"><i class="fas fa-calendar-check fa-sm"></i> Reservation</button>
            <button class="filter-btn"><i class="fas fa-qrcode fa-sm"></i> Contactless</button>
        </div>
        <div class="status-legend d-none d-lg-flex">
            <div><span class="status-dot bg-light border"></span> Blank</div>
            <div><span class="status-dot bg-info"></span> Running</div>
            <div><span class="status-dot bg-success"></span> Preparing / Ready</div>
            <div><span class="status-dot bg-warning"></span> Paid</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-muted" style="font-size: 0.75rem;">Floor Plan</span>
            <select class="floor-plan-select" id="floorPlanSelect">
                <option value="all">All Layouts</option>
                @foreach($floorPlans as $index => $plan)
                    @if(!isset($plan['is_deleted']) || !$plan['is_deleted'])
                        <option value="floor-{{ $index }}">{{ $plan['name'] }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    @php
        // Helper function to get table state and order details
        function getTableState($tableNumber, $activeOrders) {
            $order = $activeOrders->get((string)$tableNumber);
            if (!$order) return ['class' => 'blank', 'order' => null];
            
            $statusClass = 'running';
            if (in_array($order->status, ['Preparing', 'Ready'])) {
                $statusClass = 'printed';
            }
            if ($order->payment_status === 'Paid') {
                $statusClass = 'paid';
            }
            return ['class' => $statusClass, 'order' => $order];
        }
    @endphp

    <!-- Table Sections -->
    @foreach($floorPlans as $index => $plan)
        @if(isset($plan['is_deleted']) && $plan['is_deleted'])
            @continue
        @endif
        <div class="table-section floor-section border-top" id="floor-{{ $index }}">
        <div class="section-title">{{ $plan['name'] }}</div>
        <div class="table-grid">
            @for($i = $plan['start']; $i <= $plan['end']; $i++)
                @php $state = getTableState($i, $activeOrders); @endphp
                
                <a href="{{ route('pos.index') }}?table={{ $i }}" class="table-box {{ $state['class'] }}">
                    Table {{ $i }}
                    @if($state['order'])
                        <div class="table-token {{ !$state['order']->token_number ? 'token-na' : '' }}">T #{{ $state['order']->token_number ?? 'N/A' }}</div>
                        <div class="table-time">{{ \Carbon\Carbon::parse($state['order']->created_at)->diffForHumans() }}</div>
                        <div class="table-amount">₹{{ number_format($state['order']->grand_total, 0) }}</div>
                        <div class="table-actions table-actions-always" onclick="event.preventDefault(); window.open('{{ route('orders.invoice', $state['order']->id) }}', '_blank')">
                            <i class="fas fa-print" title="Print Bill"></i>
                            <i class="fas fa-eye" title="View Details" onclick="event.stopPropagation(); window.location.href='{{ route('orders.show', $state['order']->id) }}'"></i>
                        </div>
                    @else
                        <div class="table-actions">
                            <i class="fas fa-plus-circle text-success" title="New Order"></i>
                        </div>
                    @endif
                </a>
            @endfor
        </div>
    </div>
    @endforeach
</div>

@endsection

@section("scripts")
<script>
    // Refresh table status periodically (every 30 seconds)
    setTimeout(function() {
        location.reload();
    }, 30000);

    // Floor Plan Filtering
    document.getElementById('floorPlanSelect').addEventListener('change', function() {
        const selected = this.value;
        const sections = document.querySelectorAll('.floor-section');
        
        sections.forEach(section => {
            if (selected === 'all' || section.id === selected) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });

    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                showToast('error', 'Error', `Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
            });
            document.getElementById('fullscreenBtn').innerHTML = '<i class="fas fa-compress fa-lg"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                document.getElementById('fullscreenBtn').innerHTML = '<i class="fas fa-expand fa-lg"></i>';
            }
        }
    }

    // Listen for fullscreen change events to update icon
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            document.getElementById('fullscreenBtn').innerHTML = '<i class="fas fa-expand fa-lg"></i>';
        }
    });

    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const dayName = days[now.getDay()];
        document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;
    }
    
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection

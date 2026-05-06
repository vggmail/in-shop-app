<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen Display — {{ (app()->bound('tenant') ? app('tenant')->name : null) ?? 'Restaurant' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            background: #0f172a;
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        .kds-topbar {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .kds-topbar .brand { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
        .kds-topbar .kds-clock { font-size: 1.25rem; font-weight: 700; color: #f59e0b; font-variant-numeric: tabular-nums; }
        .kds-topbar .stats-pill {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 0.78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #94a3b8;
        }
        .kds-topbar .stats-pill span { color: #fff; }
        .btn-exit {
            background: #334155;
            color: #94a3b8;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-exit:hover { background: #475569; color: #fff; }
        .kds-board {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 16px;
            padding: 20px 20px 40px;
            align-items: start;
        }
        .kds-card {
            background: #1e293b;
            border-radius: 16px;
            border: 1.5px solid #334155;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .kds-card.urgent { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,0.2); }
        .kds-card.warning { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,0.15); }
        .kds-card.ready  { border-color: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,0.15); opacity: 0.6; }
        .kds-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }
        .kds-card-header {
            padding: 14px 16px 10px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 1px solid #334155;
        }
        .order-number { font-size: 1rem; font-weight: 800; color: #fff; }
        .order-type-badge {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 9px;
            border-radius: 999px;
        }
        .badge-dinein   { background: #312e81; color: #a5b4fc; }
        .badge-takeaway { background: #1c3329; color: #4ade80; }
        .badge-delivery { background: #450a0a; color: #fca5a5; }
        .badge-online   { background: #164e63; color: #7dd3fc; }
        .wait-timer { font-size: 0.72rem; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 5px; }
        .timer-ok     { color: #22c55e; }
        .timer-warn   { color: #f59e0b; }
        .timer-urgent { color: #ef4444; animation: pulse-red 1.2s ease-in-out infinite; }
        @keyframes pulse-red { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
        .kds-items { padding: 12px 16px; }
        .kds-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #0f172a;
        }
        .kds-item:last-child { border-bottom: none; }
        .item-qty {
            background: #0f172a;
            border: 1.5px solid #334155;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 800;
            color: #f8fafc;
            flex-shrink: 0;
        }
        .item-name    { font-size: 0.88rem; font-weight: 700; color: #f1f5f9; line-height: 1.3; }
        .item-variant { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }
        .item-extras  { font-size: 0.7rem;  color: #f59e0b; margin-top: 3px; }
        .kds-note {
            margin: 0 12px 12px;
            background: #451a03;
            border: 1px solid #78350f;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.75rem;
            color: #fbbf24;
        }
        .kds-card-footer {
            padding: 10px 16px;
            border-top: 1px solid #334155;
            display: flex;
            gap: 8px;
        }
        .btn-kds-ready {
            flex: 1;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-kds-ready:hover { transform: scale(1.02); box-shadow: 0 4px 16px rgba(34,197,94,0.3); }
        .btn-kds-ready:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-kds-done {
            background: #1e293b;
            border: 1.5px solid #334155;
            color: #64748b;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-kds-done:hover { border-color: #4ade80; color: #4ade80; }
        .kds-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: calc(100vh - 120px);
            color: #475569;
            text-align: center;
        }
        .kds-empty .empty-icon { font-size: 5rem; margin-bottom: 20px; opacity: 0.3; }
        .kds-empty h3 { font-size: 1.5rem; color: #64748b; margin-bottom: 8px; }
        @keyframes slideIn {
            from { opacity: 0; transform: scale(0.85) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .kds-card.new-order { animation: slideIn 0.4s ease-out; }
        .kds-filters { display: flex; gap: 8px; padding: 14px 20px 0; flex-wrap: wrap; }
        .kds-filter-btn {
            background: #1e293b;
            border: 1.5px solid #334155;
            color: #94a3b8;
            border-radius: 999px;
            padding: 5px 16px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .kds-filter-btn.active, .kds-filter-btn:hover { background: #ff4757; border-color: #ff4757; color: #fff; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="kds-topbar">
        <div class="d-flex align-items-center gap-3">
            <div class="brand"><i class="fas fa-fire-alt me-2" style="color:#f97316;"></i>Kitchen Display</div>
            <div class="stats-pill">
                <i class="fas fa-circle-notch fa-spin text-warning small"></i>
                Preparing: <span id="kds-preparing-count">—</span>
                &nbsp;|&nbsp;
                Ready: <span id="kds-ready-count">—</span>
            </div>
        </div>
        <div class="kds-clock" id="kds-clock"></div>
        <a href="{{ route('dashboard') }}" class="btn-exit"><i class="fas fa-arrow-left me-1"></i> Exit KDS</a>
    </div>

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('kds-clock').textContent =
                now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @yield('scripts')
</body>
</html>

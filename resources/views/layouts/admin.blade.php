<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ (app()->bound('tenant') ? app('tenant')->name : null) ?? 'Restaurant POS' }} - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-dark: #1e293b; 
            --accent-color: #ff4757; 
            --text-main: #334155;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
        }
        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main);
            font-size: 0.85rem;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }
        h1, h2, h3, h4, h5, h6 { 
            font-weight: 700; 
            color: #1e293b; 
            letter-spacing: -0.025em; 
        }
        h2 { font-size: 1.35rem; }
        h4 { font-size: 1.1rem; }
        h5 { font-size: 1rem; }
        
        .sidebar { width: 220px; min-width: 220px; height: 100vh; background-color: var(--primary-dark); color: #cbd5e1; box-shadow: 4px 0 10px rgba(0,0,0,0.05); position: sticky; top: 0; overflow-y: auto; z-index: 1000; transition: all 0.3s; }
        .sidebar.collapsed { width: 80px; min-width: 80px; }
        .sidebar.collapsed h5, 
        .sidebar.collapsed span, 
        .sidebar.collapsed .sidebar-item-text { 
            display: none !important; 
        }
        .sidebar.collapsed .mt-auto button { 
            width: 40px !important; 
            margin: 0 auto; 
            padding: 10px 0 !important; 
            text-align: center !important; 
            display: block !important;
        }
        .sidebar.collapsed .mt-auto button span { display: none !important; }
        .sidebar.collapsed .mt-auto button i { margin: 0 !important; }
        .sidebar.collapsed a { justify-content: center; padding: 12px; margin: 4px 10px; }
        .sidebar.collapsed a i { margin: 0; font-size: 1.1rem; width: auto; }
        .sidebar.collapsed .px-3.py-3.d-flex { justify-content: center !important; }
        .sidebar.collapsed .px-3.py-3.d-flex .me-2 { margin: 0 !important; }
        .sidebar.collapsed .mt-auto { padding: 10px !important; }

        @media (max-width: 991.98px) {
            .sidebar { position: fixed; left: -220px; width: 220px; }
            .sidebar.active-mobile { left: 0; z-index: 1050; }
            .sidebar.collapsed { width: 220px; min-width: 220px; } /* Reset on mobile if manually collapsed on desktop */
            .sidebar.collapsed h5, .sidebar.collapsed span, .sidebar.collapsed .mt-auto button span { display: inline-block !important; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
        }
        .sidebar a { color: #94a3b8; text-decoration: none; display: flex; align-items: center; padding: 10px 18px; font-weight: 500; transition: all 0.3s; border-radius: 10px; margin: 4px 15px; font-size: 0.825rem; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; transform: translateX(3px); }
        .sidebar a i { width: 20px; font-size: 0.9rem; }
        .sidebar .active { background-color: var(--accent-color) !important; color: white !important; box-shadow: 0 4px 12px rgba(255, 71, 87, 0.2); }
        .main-content { min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s; }
        .top-navbar { background: #fff; padding: 10px 30px; border-bottom: 1px solid #e2e8f0; }
        .content-area { padding: 25px; flex-grow: 1; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        /* Compact Button System */
        .btn { font-weight: 600; border-radius: 6px; padding: 6px 16px; font-size: 0.825rem; transition: all 0.2s ease; }
        .btn-lg { padding: 8px 20px; font-size: 0.9rem; }
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }
        .btn-primary { background-color: var(--accent-color); border-color: var(--accent-color); }
        .btn-primary:hover { background-color: #e03d4b; border-color: #e03d4b; box-shadow: 0 4px 12px rgba(255, 71, 87, 0.15); }
        
        .notification-bell { position: relative; cursor: pointer; padding: 8px; border-radius: 10px; transition: all 0.2s; background: #f8fafc; border: 1px solid #e2e8f0; }
        .notification-bell:hover { background: #f1f5f9; }
        .bell-badge { position: absolute; top: 6px; right: 6px; width: 7px; height: 7px; border-radius: 50%; border: 2px solid #fff; background: #ef4444; display: none; }
        .avatar-circle { width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem; }
    </style>
    @yield("styles")
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 1040; display: none;"></div>
    <div class="row g-0 flex-nowrap min-vh-100">
        <div class="col-auto sidebar" id="admin-sidebar">
                <div class="px-3 py-3 d-flex align-items-center">
                    @if(isset($tenant_info) && $tenant_info->logo)
                        <img src="{{ asset($tenant_info->logo) }}" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                    @else
                        <div class="bg-danger rounded-circle me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; min-width: 32px;">
                            <i class="fas fa-utensils text-white small"></i>
                        </div>
                    @endif
                    <h5 class="text-white fw-bold mb-0 text-truncate">{{ $tenant_info->name ?? 'Fast Food' }}</h5>
                </div>
                
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="{{ route('pos.index') }}" class="text-warning"><i class="fas fa-cash-register"></i> <span>POS Screen</span></a>
                <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="fas fa-box"></i> <span>Menu Items</span></a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="fas fa-layer-group"></i> <span>Categories</span></a>
                <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }} d-flex justify-content-between">
                    <div><i class="fas fa-shopping-cart"></i> <span>Orders</span></div>
                    <span class="badge bg-danger rounded-pill" id="sidebar-pending-count">{{ $pending_orders_count > 0 ? $pending_orders_count : '' }}</span>
                </a>
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> <span>Customers</span></a>
                <a href="{{ route('coupons.index') }}" class="{{ request()->routeIs('coupons.*') ? 'active' : '' }}"><i class="fas fa-tag"></i> <span>Coupons</span></a>
                <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}"><i class="fas fa-wallet"></i> <span>Expenses</span></a>
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> <span>Payments</span></a>
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> <span>Reports</span></a>
                
                @if(auth()->user()->role_id == 1)
                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}"><i class="fas fa-store"></i> <span>Store Settings</span></a>
                <a href="{{ route('settings.payments') }}" class="{{ request()->routeIs('settings.payments') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> <span>Payment Settings</span></a>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fas fa-user-shield"></i> <span>Staff Management</span></a>
                <a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'active' : '' }}"><i class="fas fa-history"></i> <span>Activity Logs</span></a>
                @endif
                
                <div class="mt-auto p-3">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm w-100 border-0 text-start px-3 py-2"><i class="fas fa-sign-out-alt me-2"></i> <span>Logout</span></button>
                    </form>
                </div>
            </div>
            
            <div class="col main-content">
                <nav class="top-navbar d-flex justify-content-between align-items-center shadow-sm">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link link-dark d-lg-none p-0 me-3 shadow-none" id="sidebar-toggler"><i class="fas fa-bars fs-4"></i></button>
                        <button class="btn btn-link link-dark d-none d-lg-block p-0 me-3 shadow-none" id="sidebar-toggler-desktop"><i class="fas fa-indent fs-4"></i></button>
                        <h5 class="mb-0 fw-bold text-dark opacity-75">Control Center</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="notification-bell me-3" id="notif-trigger" data-bs-toggle="dropdown">
                            <i class="fas fa-bell text-secondary"></i>
                            <span class="bell-badge" id="bell-dot"></span>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" style="width: 320px; border-radius: 15px; overflow: hidden;">
                                <div class="bg-primary p-3 text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Notifications</h6>
                                    <span class="badge bg-white text-primary rounded-pill small" id="notif-count-badge">0 New</span>
                                </div>
                                <div id="notif-content" class="p-3 text-center text-muted small bg-white" style="max-height: 400px; overflow-y: auto;">
                                    <div class="py-4">
                                        <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <div class="d-flex align-items-center pointer ms-2" data-bs-toggle="dropdown" style="cursor: pointer;">
                                <div class="avatar-circle me-2">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="d-none d-lg-block">
                                    <div class="fw-bold text-dark small">{{ auth()->user()->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ auth()->user()->role->name ?? 'Staff' }}</div>
                                </div>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2">
                                <li>
                                    <a class="dropdown-item py-2 fw-bold small text-dark" href="{{ route('admin.password') }}">
                                        <i class="fas fa-key me-2 text-muted"></i> Change Password
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 fw-bold small text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <div class="content-area">
                    <x-flash-messages />
                    @yield("content")
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showToast(icon, title, text, timer = 3000) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                timer: timer,
                showConfirmButton: false,
                showCloseButton: true,
                toast: true,
                position: 'top-end',
                timerProgressBar: true,
                background: '#fff',
                iconColor: icon === 'success' ? '#10b981' : (icon === 'error' ? '#ef4444' : '#f59e0b')
            });
        }

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#ff4757',
                showCloseButton: true,
                borderRadius: '15px'
            });
        }

        function confirmAction(title, text, callback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4757',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, proceed!',
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        let lastOrderId = {{ $latest_order_id ?? 0 }};
        function checkNewOrders() {
            $.ajax({
                url: '{{ route("orders.check-pending") }}',
                cache: false,
                success: function(data) {
                    if (data.count > 0) {
                        $('#sidebar-pending-count').text(data.count).show();
                        $('#notif-count-badge').text(data.count + ' New');
                    } else {
                        $('#sidebar-pending-count').hide();
                        $('#notif-count-badge').text('0 New');
                    }

                    if (data.latest_id > lastOrderId) {
                        $('#bell-dot').show();
                        $('#notif-content').html(`
                            <div class="list-group list-group-flush shadow-sm rounded-3 overflow-hidden">
                                <div class="list-group-item border-0 bg-light-primary p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-${data.latest_status == 'Payment Failed' ? 'danger' : 'success'} text-white rounded-circle p-2 me-3"><i class="fas fa-shopping-basket"></i></div>
                                        <div>
                                            <div class="fw-bold text-dark">${data.latest_status == 'Payment Failed' ? 'Payment Failed!' : 'New Order Received!'}</div>
                                            <div class="small text-muted">${data.latest_order}</div>
                                        </div>
                                    </div>
                                    <button onclick="window.location.href='/cp/orders/${data.latest_id}'" class="btn btn-primary btn-sm w-100 mt-3 rounded-pill shadow-sm">Process Now</button>
                                </div>
                            </div>
                        `);
                        showToast(data.latest_status == 'Payment Failed' ? 'error' : 'success', data.latest_status == 'Payment Failed' ? 'Order Failed!' : 'Incoming Order!', (data.latest_status == 'Payment Failed' ? 'Payment failed for ' : 'New order ') + data.latest_order);
                        new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play().catch(() => {});
                    }
                    lastOrderId = data.latest_id;
                }
            });
        }
        setInterval(checkNewOrders, 5000); 
        $(document).ready(function() {
            // Check localStorage for sidebar state
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                $('#admin-sidebar').addClass('collapsed');
                $('#sidebar-toggler-desktop i').removeClass('fa-indent').addClass('fa-outdent');
            }

            $('#sidebar-toggler, #sidebar-overlay').on('click', function() {
                $('#admin-sidebar').toggleClass('active-mobile');
                $('#sidebar-overlay').toggle();
            });

            $('#sidebar-toggler-desktop').on('click', function() {
                $('#admin-sidebar').toggleClass('collapsed');
                let isCollapsed = $('#admin-sidebar').hasClass('collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
                
                // Toggle icon
                if (isCollapsed) {
                    $(this).find('i').removeClass('fa-indent').addClass('fa-outdent');
                } else {
                    $(this).find('i').removeClass('fa-outdent').addClass('fa-indent');
                }
            });
        });

        $('#notif-trigger').on('click', () => $('#bell-dot').hide());
    </script>
    @yield("scripts")
</body>
</html>
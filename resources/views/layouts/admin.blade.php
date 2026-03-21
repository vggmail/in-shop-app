<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #1e293b; color: #cbd5e1; box-shadow: 2px 0 5px rgba(0,0,0,0.1); position: sticky; top: 0; overflow-y: auto; z-index: 1000; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px 20px; font-weight: 500; transition: all 0.2s; border-radius: 8px; margin: 4px 12px; }
        .sidebar a:hover, .sidebar a.active { background-color: #334155; color: #fff; }
        .sidebar a.text-warning { color: #fbbf24 !important; }
        .main-content { padding: 0; }
        .content-area { padding: 20px; }
        .top-navbar { background: #fff; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .notification-bell { position: relative; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.2s; }
        .notification-bell:hover { background: #f1f5f9; }
        .bell-badge { position: absolute; top: 5px; right: 5px; width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: none; }
        .badge-count { font-size: 0.7rem; padding: 0.35em 0.65em; }
    </style>
    @yield("styles")
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            <div class="col-auto col-md-3 col-xl-2 sidebar">
                <h4 class="text-center my-4 text-white"><i class="fas fa-utensils text-danger"></i> Fast Food</h4>
                <a href="{{ route("dashboard") }}"><i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="{{ route("pos.index") }}" class="text-warning"><i class="fas fa-fw fa-cash-register me-2"></i> POS Screen</a>
                <a href="{{ route("items.index") }}"><i class="fas fa-fw fa-hamburger me-2"></i> Menu Items</a>
                <a href="{{ route("orders.index") }}" class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-fw fa-receipt me-2"></i> Orders</span>
                    <span class="badge bg-danger rounded-pill badge-count" id="sidebar-pending-count">{{ $pending_orders_count > 0 ? $pending_orders_count : '' }}</span>
                </a>
                <a href="{{ route("customers.index") }}"><i class="fas fa-fw fa-users me-2"></i> Customers</a>
                <a href="{{ route("coupons.index") }}"><i class="fas fa-fw fa-ticket-alt me-2"></i> Coupons</a>
                <a href="{{ route("expenses.index") }}"><i class="fas fa-fw fa-wallet me-2"></i> Expenses</a>
                <a href="{{ route("payments.index") }}"><i class="fas fa-fw fa-credit-card me-2"></i> Payments</a>
                <a href="{{ route("reports.index") }}"><i class="fas fa-fw fa-chart-line me-2"></i> Reports</a>
                @if(auth()->user()->role_id == 1)
                <a href="{{ route("users.index") }}"><i class="fas fa-fw fa-users-cog me-2"></i> Staff Management</a>
                <a href="{{ route("logs.index") }}"><i class="fas fa-fw fa-history me-2"></i> Activity Logs</a>
                @endif
                <hr class="border-secondary">
                <form action="{{ route("logout") }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-white text-decoration-none px-4"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
            <div class="col main-content">
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Management Console</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="notification-bell me-3" id="notif-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell text-secondary fa-lg"></i>
                            <span class="bell-badge" id="bell-dot"></span>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-3" style="width: 300px;" id="notif-dropdown">
                                <li class="dropdown-header">Notifications</li>
                                <li><hr class="dropdown-divider"></li>
                                <li id="notif-content" class="text-center py-2 text-muted small">No new notifications</li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <div class="d-flex align-items-center pointer" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="fw-600 text-dark">{{ auth()->user()->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-area">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @yield("content")
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let lastOrderId = {{ $latest_order_id ?? 0 }};
        function checkNewOrders() {
            $.get('{{ route("orders.check-pending") }}', function(data) {
                if (data.count > 0) $('#sidebar-pending-count').text(data.count);
                else $('#sidebar-pending-count').text('');

                if (data.latest_id > lastOrderId) {
                    $('#bell-dot').show();
                    $('#notif-content').html(`
                        <div class="d-flex align-items-center p-2 mb-2 bg-light rounded text-start">
                            <div class="bg-success text-white rounded-circle p-2 me-2"><i class="fas fa-shopping-basket"></i></div>
                            <div>
                                <div class="fw-bold text-dark small">New Order Received!</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">${data.latest_order}</div>
                            </div>
                        </div>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary w-100 mt-2">View Orders</a>
                    `);
                    Swal.fire({
                        title: 'New Order!',
                        text: 'Order ' + data.latest_order + ' has been placed.',
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play().catch(() => {});
                }
                lastOrderId = data.latest_id;
            });
        }
        setInterval(checkNewOrders, 5000); // Check every 5s for better responsiveness
        $('#notif-trigger').on('click', () => $('#bell-dot').hide());
    </script>
    @yield("scripts")
</body>
</html>
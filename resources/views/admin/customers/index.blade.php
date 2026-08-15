@extends("layouts.admin")
@section("content")
<div class="d-flex flex-column flex-lg-row align-items-center justify-content-between mb-4 mt-2 gap-3">
    <!-- Left: Title -->
    <div class="flex-shrink-0">
        <h2 class="mb-0 fw-bold fs-4"><i class="fas fa-users text-primary me-2"></i> CRM - Loyal Customers</h2>
    </div>

    <!-- Center: Search & View (Centered) -->
    <div class="d-flex flex-grow-1 align-items-center justify-content-center gap-2 w-100">
        <div class="input-group rounded-pill overflow-hidden shadow-sm border bg-white" style="max-width: 300px;">
            <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="customerSearch" class="form-control border-0 px-1" style="font-size: 14px; box-shadow: none;" placeholder="Search customers..." onkeyup="filterCustomers(this.value)">
        </div>
        <div class="btn-group shadow-sm border rounded-pill overflow-hidden bg-white p-1" style="background: #f8fafc; height: 42px;">
            <button class="btn btn-sm btn-light border-0 px-3 active-view" id="btnList" onclick="setViewMode('list')"><i class="fas fa-list"></i></button>
            <button class="btn btn-sm btn-light border-0 px-3" id="btnGrid" onclick="setViewMode('grid')"><i class="fas fa-th-large"></i></button>
        </div>
    </div>

    <!-- Right: Blank Placeholder for Balance -->
    <div class="flex-shrink-0 d-none d-lg-block" style="width: 200px;"></div>
</div>

<div class="row" id="gridView">
    @foreach($customers as $c)
    <div class="col-md-3 mb-4 customer-search-node">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-user-tie fa-2x text-primary border border-2 border-primary rounded-circle p-2"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark customer-search-name">{{ $c->name ?: "Guest" }}</h5>
                    <p class="text-muted small mb-0 customer-search-phone"><i class="fas fa-id-badge me-1"></i> {{ $c->phone }}</p>
                </div>
                
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Orders</span>
                        <span class="fw-bold small">{{ $c->total_orders }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Total Spent</span>
                        <span class="fw-bold small text-success">₹{{ number_format($c->total_spending, 2) }}</span>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ route('orders.index', ['customer_search' => $c->phone]) }}" class="btn btn-xs btn-outline-primary flex-fill rounded-pill py-1 d-flex align-items-center justify-content-center" style="font-size: 11px;"><i class="fas fa-history small me-1"></i> Order History</a>
                    
                    <form action="{{ route('customers.reset-pin', $c->id) }}" method="POST" class="flex-fill">
                        @csrf
                        <button type="button" class="btn btn-xs btn-outline-warning w-100 rounded-pill py-1 fw-bold" style="font-size: 11px;" 
                                onclick="confirmAction('Reset PIN', 'This will clear the customer\'s PIN. They will be asked to set a new one next time they log in. Proceed?', () => this.closest('form').submit())">
                            <i class="fas fa-key small me-1"></i> Reset PIN
                        </button>
                    </form>

                    <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="flex-fill">
                        @csrf @method("DELETE")
                        <button type="button" class="btn btn-xs btn-outline-danger w-100 rounded-pill py-1" style="font-size: 11px;"
                                onclick="confirmAction('Delete Customer', 'Are you sure you want to remove this customer?', () => this.closest('form').submit())">
                            <i class="fas fa-trash small"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 d-none" id="listView">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase" style="font-size: 11px;">
                    <tr>
                        <th class="ps-4 py-3">Customer</th>
                        <th class="py-3 text-center">Orders</th>
                        <th class="py-3">Total Spending</th>
                        <th class="py-3">Status</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                    <tr class="customer-search-node">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-primary small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark customer-search-name">{{ $c->name ?: "Guest Customer" }}</div>
                                    <div class="small text-muted customer-search-phone">{{ $c->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark px-3 rounded-pill border">{{ $c->total_orders }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-success">₹{{ number_format($c->total_spending, 2) }}</div>
                        </td>
                        <td>
                            @if($c->pin)
                                <span class="badge bg-success-subtle text-success border-0 px-2 py-1"><i class="fas fa-shield-alt me-1"></i> Secured</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border-0 px-2 py-1"><i class="fas fa-unlock me-1"></i> Open PIN</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('orders.index', ['customer_search' => $c->phone]) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="History">
                                    <i class="fas fa-history text-primary me-1"></i> <small class="fw-bold">History</small>
                                </a>
                                <form action="{{ route('customers.reset-pin', $c->id) }}" method="POST">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Reset PIN"
                                            onclick="confirmAction('Reset PIN', 'Clear this customer PIN?', () => this.closest('form').submit())">
                                        <i class="fas fa-key text-warning me-1"></i> <small class="fw-bold">Reset</small>
                                    </button>
                                </form>
                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST">
                                    @csrf @method("DELETE")
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3"
                                            onclick="confirmAction('Delete Customer', 'Are you sure?', () => this.closest('form').submit())">
                                        <i class="fas fa-trash text-danger me-1"></i> <small class="fw-bold">Del</small>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($customers->isEmpty())
<div class="col-12 text-center py-5">
    <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
    <h5 class="text-muted">No customers registered yet.</h5>
    <p class="small">Add customers directly via the POS screen during checkout.</p>
</div>
@endif

@endsection

@section("scripts")
<script>
    function setViewMode(mode) {
        localStorage.setItem('customer_view_mode', mode);
        if (mode === 'list') {
            $('#gridView').addClass('d-none');
            $('#listView').removeClass('d-none');
            $('#btnList').addClass('active-view bg-primary text-white').removeClass('btn-light');
            $('#btnGrid').addClass('btn-light').removeClass('active-view bg-primary text-white');
        } else {
            $('#listView').addClass('d-none');
            $('#gridView').removeClass('d-none');
            $('#btnGrid').addClass('active-view bg-primary text-white').removeClass('btn-light');
            $('#btnList').addClass('btn-light').removeClass('active-view bg-primary text-white');
        }
    }

    function filterCustomers(query) {
        query = query.toLowerCase().trim();
        if (query.length > 0 && query.length < 3) {
            $('.customer-search-node').removeClass('d-none');
            return;
        }

        $('.customer-search-node').each(function() {
            let name = $(this).find('.customer-search-name').text().toLowerCase();
            let phone = $(this).find('.customer-search-phone').text().toLowerCase();
            if(query === "" || name.includes(query) || phone.includes(query)) {
                $(this).removeClass('d-none');
            } else {
                $(this).addClass('d-none');
            }
        });
    }

    $(document).ready(function () {
        let mode = localStorage.getItem('customer_view_mode') || 'list';
        setViewMode(mode);
    });
</script>
@endsection

<?php

$dirV = __DIR__ . '/resources/views/admin';

// PRODCUTS
file_put_contents("$dirV/products/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Products</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Product</button>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($products as $p)
            <tr>
                <td>-</td>
                <td>{{ $p->name }}<br><small class="text-muted">{{ $p->category->name }}</small></td>
                <td>{{ $p->sku }}</td>
                <td>${{ $p->price }}</td>
                <td>
                    @if($p->stock_quantity <= $p->low_stock_alert)
                        <span class="badge bg-danger">{{ $p->stock_quantity }}</span>
                    @else
                        <span class="badge bg-success">{{ $p->stock_quantity }}</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="edit({{ $p->id }})">Edit</button>
                    <form action="{{ route(\'products.destroy\', $p->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-danger" onclick="return confirm(\'Sure?\')">Del</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route(\'products.store\') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Add Product</h5></div>
    <div class="modal-body p-3">
        <select name="category_id" class="form-control mb-2" required>
            <option value="">Select Category</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
        <input type="text" name="sku" class="form-control mb-2" placeholder="SKU" required>
        <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
        <input type="number" name="stock_quantity" class="form-control mb-2" placeholder="Stock Qty" required>
        <input type="number" name="low_stock_alert" value="5" class="form-control mb-2" placeholder="Low Stock Alert" required>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div></div>
@endsection');


// CUSTOMERS
file_put_contents("$dirV/customers/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Customers</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Customer</button>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Total Orders</th><th>Total $$</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($customers as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->total_orders }}</td>
                <td>${{ $c->total_purchase }}</td>
                <td>
                    <form action="{{ route(\'customers.destroy\', $c->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-danger" onclick="return confirm(\'Sure?\')">Del</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route(\'customers.store\') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Add Customer</h5></div>
    <div class="modal-body p-3">
        <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
        <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" required>
        <input type="email" name="email" class="form-control mb-2" placeholder="Email (optional)">
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div></div>
@endsection');


// COUPONS
file_put_contents("$dirV/coupons/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Coupons</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Coupon</button>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Expiry</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($coupons as $c)
            <tr>
                <td>{{ $c->code }}</td>
                <td>{{ ucfirst($c->discount_type) }}</td>
                <td>{{ $c->discount_type=="fixed" ? "$".$c->value : $c->value."%" }}</td>
                <td>${{ $c->min_order_amount }}</td>
                <td>{{ $c->expiry_date }}</td>
                <td>
                    <form action="{{ route(\'coupons.destroy\', $c->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-danger" onclick="return confirm(\'Sure?\')">Del</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route(\'coupons.store\') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Add Coupon</h5></div>
    <div class="modal-body p-3">
        <input type="text" name="code" class="form-control mb-2" placeholder="Coupon Code" required>
        <select name="discount_type" class="form-control mb-2">
            <option value="fixed">Fixed</option>
            <option value="percentage">Percentage</option>
        </select>
        <input type="number" step="0.01" name="value" class="form-control mb-2" placeholder="Discount Value" required>
        <input type="number" step="0.01" name="min_order_amount" class="form-control mb-2" placeholder="Min Order Amount">
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control mb-2">
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div></div>
@endsection');


// ORDERS
file_put_contents("$dirV/orders/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Orders</h2>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($orders as $o)
            <tr>
                <td>{{ $o->order_number }}</td>
                <td>{{ $o->customer ? $o->customer->name : \'Walking Customer\' }}</td>
                <td>${{ $o->grand_total }}</td>
                <td>
                    <span class="badge {{ $o->payment_status == \'Paid\' ? \'bg-success\' : \'bg-warning\' }}">{{ $o->payment_status }}</span>
                </td>
                <td>{{ $o->created_at->format(\'Y-m-d H:i\') }}</td>
                <td>
                    <a href="{{ route(\'orders.show\', $o->id) }}" class="btn btn-sm btn-info">View</a>
                    <a target="_blank" href="{{ route(\'orders.invoice\', $o->id) }}" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Print</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links("pagination::bootstrap-5") }}
</div>
@endsection');


// PAYMENTS
file_put_contents("$dirV/payments/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Payment History</h2>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Payment ID</th><th>Order #</th><th>Method</th><th>Paid</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($payments as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->order->order_number }}</td>
                <td>{{ $p->payment_method }}</td>
                <td>${{ $p->paid_amount }}</td>
                <td>
                    <span class="badge {{ $p->payment_status == \'Paid\' ? \'bg-success\' : \'bg-warning\' }}">{{ $p->payment_status }}</span>
                </td>
                <td>{{ $p->payment_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection');


// REPORTS
file_put_contents("$dirV/reports/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Reports</h2>
</div>
<div class="card bg-white p-3 text-center py-5">
    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
    <h4 class="text-muted">Report filters and export (CSV/Excel) module can be plugged in here</h4>
</div>
@endsection');

echo "CRUD Views Generated.\n";

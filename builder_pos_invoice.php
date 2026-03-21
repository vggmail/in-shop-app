<?php

$dirV = __DIR__ . '/resources/views/admin';

// pos/index.blade.php
file_put_contents("$dirV/pos/index.blade.php", '@extends("layouts.admin")

@section("styles")
<style>
    .product-card { cursor: pointer; transition: transform 0.2s; }
    .product-card:hover { transform: scale(1.05); }
    .cart-item { font-size: 0.9rem; }
</style>
@endsection

@section("content")
<div class="row">
    <!-- Products List -->
    <div class="col-md-7">
        <h4 class="mb-3">Products</h4>
        <div class="row g-2" style="max-height: 80vh; overflow-y: auto;">
            @foreach($products as $p)
            <div class="col-md-3 col-6">
                <div class="card product-card text-center p-2 mb-2" onclick="addToCart({{ $p->id }}, \'{{ addslashes($p->name) }}\', {{ $p->price }})">
                    <img src="https://via.placeholder.com/100" class="img-fluid rounded mb-2">
                    <h6 class="text-truncate mb-1" style="font-size: 0.85rem;" title="{{ $p->name }}">{{ $p->name }}</h6>
                    <span class="badge bg-primary">${{ $p->price }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Cart / Checkout -->
    <div class="col-md-5">
        <div class="card p-3 shadow-sm" style="min-height: 80vh;">
            <h4 class="mb-3">Current Order</h4>
            <div class="table-responsive border p-2 mb-3 bg-light rounded" style="min-height: 300px; max-height:300px; overflow-y:auto;">
                <table class="table table-sm table-borderless" id="cartTable">
                    <thead><tr class="border-bottom"><th>Item</th><th width="80">Qty</th><th width="60">Total</th><th width="40"></th></tr></thead>
                    <tbody id="cartBody"></tbody>
                </table>
            </div>
            
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <input type="text" id="couponCode" class="form-control" placeholder="Coupon Code">
                    <button class="btn btn-dark" onclick="applyCoupon()">Apply</button>
                </div>
                <small id="couponMsg" class="d-block mt-1"></small>
            </div>
            
            <table class="table table-sm mb-3 font-weight-bold">
                <tr><td>Subtotal</td><td class="text-end">$<span id="subTotal">0.00</span></td></tr>
                <tr class="text-danger"><td>Discount (-)</td><td class="text-end">$<span id="discount">0.00</span></td></tr>
                <tr class="fs-5" style="border-top: 2px solid #333;"><td>Grand Total</td><td class="text-end text-success">$<span id="grandTotal">0.00</span></td></tr>
            </table>
            
            <div class="mb-3">
                <select id="customer_id" class="form-control mb-2 form-select-sm">
                    <option value="">Walk-in Customer</option>
                    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} - {{ $c->phone }}</option>@endforeach
                </select>
                <select id="payment_method" class="form-control form-select-sm">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="UPI">UPI</option>
                    <option value="Pending">Pending (Unpaid)</option>
                </select>
            </div>
            
            <button class="btn btn-success w-100 py-2 fs-5" onclick="processOrder()"><i class="fas fa-check-circle"></i> Complete Order</button>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    let cart = {};
    let discountVal = 0;
    
    function addToCart(id, name, price) {
        if(cart[id]) { cart[id].qty++; cart[id].total = cart[id].qty * price; }
        else { cart[id] = {id: id, name: name, price: price, qty: 1, total: price}; }
        renderCart();
    }
    
    function updateQty(id, qty) {
        qty = parseInt(qty);
        if(qty <= 0) delete cart[id];
        else { cart[id].qty = qty; cart[id].total = qty * cart[id].price; }
        renderCart();
    }
    
    function renderCart() {
        let html = ""; let sub = 0;
        for(let id in cart) {
            let item = cart[id]; sub += item.total;
            html += `<tr class="cart-item border-bottom">
                <td>${item.name}<br><small class="text-muted">$${item.price}</small></td>
                <td><input type="number" min="1" class="form-control form-control-sm text-center" style="width:60px;" value="${item.qty}" onchange="updateQty(${id}, this.value)"></td>
                <td class="pt-2">$${item.total.toFixed(2)}</td>
                <td><button class="btn btn-sm text-danger" onclick="updateQty(${id}, 0)"><i class="fas fa-times"></i></button></td>
            </tr>`;
        }
        $("#cartBody").html(html);
        $("#subTotal").text(sub.toFixed(2));
        calcTotal(sub);
    }
    
    function applyCoupon() {
        let code = $("#couponCode").val();
        if(!code) return;
        $.post("{{ route(\'coupons.check\') }}", {_token: "{{ csrf_token() }}", code: code}, function(res) {
            if(res.status) {
                let sub = parseFloat($("#subTotal").text());
                let c = res.coupon;
                if(c.min_order_amount > 0 && sub < c.min_order_amount) {
                    $("#couponMsg").html(`<span class="text-danger">Min order amount: $${c.min_order_amount}</span>`);
                    return;
                }
                if(c.discount_type == "fixed") discountVal = parseFloat(c.value);
                else discountVal = sub * (parseFloat(c.value) / 100);
                
                $("#couponMsg").html(`<span class="text-success">Coupon Applied! (-$${discountVal.toFixed(2)})</span>`);
                calcTotal(sub);
            } else {
                $("#couponMsg").html(`<span class="text-danger">${res.msg}</span>`);
            }
        });
    }
    
    function calcTotal(sub) {
        $("#discount").text(discountVal.toFixed(2));
        let gt = sub - discountVal;
        $("#grandTotal").text(gt > 0 ? gt.toFixed(2) : "0.00");
    }
    
    function processOrder() {
        if(Object.keys(cart).length === 0) return alert("Cart is empty!");
        let items = [];
        for(let id in cart) items.push({ product_id: id, quantity: cart[id].qty, price: cart[id].price, total: cart[id].total });
        
        let sub = parseFloat($("#subTotal").text());
        let gt = parseFloat($("#grandTotal").text());
        
        let data = {
            _token: "{{ csrf_token() }}",
            customer_id: $("#customer_id").val(),
            payment_method: $("#payment_method").val(),
            items: items,
            total_amount: sub,
            discount_amount: discountVal,
            grand_total: gt
        };
        
        $.post("{{ route(\'pos.store\') }}", data, function(res) {
            if(res.status) {
                if(confirm(res.msg + "\\nDo you want to print invoice?")) {
                    window.open("/orders/" + res.order_id + "/invoice", "_blank");
                }
                location.reload();
            } else {
                alert(res.msg);
            }
        });
    }
</script>
@endsection');


// orders/show.blade.php
file_put_contents("$dirV/orders/show.blade.php", '@extends("layouts.admin")
@section("content")
<div class="card p-4">
    <div class="d-flex justify-content-between">
        <h2>Order: {{ $order->order_number }}</h2>
        <a href="{{ route("orders.invoice", $order->id) }}" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> Print Invoice</a>
    </div>
    <hr>
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Customer Details</h5>
            @if($order->customer)
                <p class="mb-0"><strong>Name:</strong> {{ $order->customer->name }}</p>
                <p class="mb-0"><strong>Phone:</strong> {{ $order->customer->phone }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $order->customer->email ?? \'N/A\' }}</p>
            @else
                <p>Walk-in Customer</p>
            @endif
        </div>
        <div class="col-md-6 text-md-end">
            <h5>Payment Details</h5>
            <p class="mb-0"><strong>Date:</strong> {{ $order->created_at->format(\'Y-m-d H:i A\') }}</p>
            <p class="mb-0"><strong>Method:</strong> {{ $order->payment_method }}</p>
            <p class="mb-0"><strong>Status:</strong> 
                <span class="badge {{ $order->payment_status == \'Paid\' ? \'bg-success\' : \'bg-warning\' }}">{{ $order->payment_status }}</span>
            </p>
        </div>
    </div>
    <table class="table table-bordered">
        <thead class="bg-light"><tr><th>Product</th><th>Price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product ? $item->product->name : \'Removed Product\' }}</td>
                <td>${{ number_format($item->price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-end">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Subtotal:</th><th class="text-end">${{ number_format($order->total_amount, 2) }}</th></tr>
            <tr><th colspan="3" class="text-end text-danger">Discount:</th><th class="text-end text-danger">-${{ number_format($order->discount_amount, 2) }}</th></tr>
            <tr class="fs-5"><th colspan="3" class="text-end">Grand Total:</th><th class="text-end text-success">${{ number_format($order->grand_total, 2) }}</th></tr>
        </tfoot>
    </table>
</div>
@endsection');


// orders/invoice.blade.php
file_put_contents("$dirV/orders/invoice.blade.php", '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: "Courier New", Courier, monospace; width: 80mm; margin: 0 auto; color: #000; background: #fff; font-size: 12px; }
        .invoice-box { padding: 5mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; margin-bottom: 5px; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; text-align: left; }
        .shop-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .shop-info { font-size: 11px; margin-bottom: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align:center; padding: 10px; background:#f1f1f1; border-bottom:1px solid #ccc; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size:16px; cursor:pointer;">Print Invoice</button>
    </div>
    <div class="invoice-box">
        <div class="text-center border-bottom">
            <div class="shop-name">MY SUPER SHOP</div>
            <div class="shop-info">
                123 Main Street, City<br>
                Phone: +1 234 567 8900<br>
                GST/VAT: 9876543210
            </div>
        </div>
        
        <div style="margin: 5px 0;">
            <b>Order:</b> {{ $order->order_number }}<br>
            <b>Date:</b> {{ $order->created_at->format("d-m-Y H:i") }}<br>
            <b>Customer:</b> {{ $order->customer ? $order->customer->name : "Walk-in" }}<br>
            @if($order->customer && $order->customer->phone)
            <b>Phone:</b> {{ $order->customer->phone }}
            @endif
        </div>
        
        <div class="border-top border-bottom">
            <table>
                <thead>
                    <tr>
                        <th width="40%">Item</th>
                        <th width="20%">Qty</th>
                        <th width="20%">Price</th>
                        <th width="20%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ substr($item->product ? $item->product->name : "Item", 0, 15) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            <tr class="fw-bold fs-6">
                <td style="font-size: 14px; padding-top: 5px;">GRAND TOTAL</td>
                <td class="text-right" style="font-size: 14px; padding-top: 5px;">{{ number_format($order->grand_total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding-top: 5px;">Payment</td>
                <td class="text-right" style="padding-top: 5px;">{{ $order->payment_method }} ({{ $order->payment_status }})</td>
            </tr>
        </table>
        
        <div class="text-center border-top" style="margin-top: 10px; padding-top: 10px;">
            <p style="margin: 0; font-size:11px;">Thank you for your purchase!</p>
            <p style="margin: 3px 0; font-size:10px;">Please visit again.</p>
            <div style="font-size: 8px; margin-top:5px;">Software by Developer</div>
        </div>
    </div>
</body>
</html>');

echo "POS and Invoice Views Generated.\n";

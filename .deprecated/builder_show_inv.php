<?php
$dirV = __DIR__ . '/resources/views/admin';

// orders/show.blade.php
file_put_contents("$dirV/orders/show.blade.php", '@extends("layouts.admin")
@section("content")
<div class="card p-4 shadow border-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="fas fa-receipt text-muted me-2"></i> Order: {{ $order->order_number }}</h2>
        <a href="{{ route("orders.invoice", $order->id) }}" target="_blank" class="btn btn-dark"><i class="fas fa-print"></i> Print Kitchen/Customer Receipt</a>
    </div>
    
    <div class="row mb-4 bg-light p-3 rounded">
        <div class="col-md-4 border-end">
            <h5 class="fw-bold mb-3 text-secondary">Dining Details</h5>
            <p class="mb-1"><strong>Type:</strong> 
                @if($order->order_type == "Takeaway")
                    <span class="badge bg-secondary"><i class="fas fa-shopping-bag"></i> Takeaway</span>
                @else
                    <span class="badge bg-primary"><i class="fas fa-chair"></i> Dine-In</span> (Table: <strong>{{ $order->table_number }}</strong>)
                @endif
            </p>
            <p class="mb-0"><strong>Status:</strong> 
                <span class="badge bg-info text-dark">{{ $order->status }}</span>
            </p>
        </div>
        <div class="col-md-4 border-end ps-4">
            <h5 class="fw-bold mb-3 text-secondary">Payment Info</h5>
            <p class="mb-1"><strong>Time:</strong> <i class="far fa-clock"></i> {{ $order->created_at->format(\'Y-m-d H:i A\') }}</p>
            <p class="mb-1"><strong>Method:</strong> {{ $order->payment_method }}</p>
            <p class="mb-0"><strong>Status:</strong> 
                <span class="badge {{ $order->payment_status == \'Paid\' ? \'bg-success\' : \'bg-danger\' }}">{{ $order->payment_status }}</span>
            </p>
        </div>
        <div class="col-md-4 ps-4">
            <h5 class="fw-bold mb-3 text-secondary">Kitchen Note</h5>
            <div class="p-2 border rounded bg-white text-danger fw-bold fst-italic shadow-sm" style="min-height: 60px;">
                {{ $order->note ? "🔊 " . $order->note : "No special instructions" }}
            </div>
        </div>
    </div>
    
    <table class="table table-hover">
        <thead class="table-dark"><tr><th>Item Description</th><th class="text-center">Rate</th><th class="text-center">Qty</th><th class="text-end">Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="align-middle">
                    <span class="fw-bold fs-6">{{ $item->item ? $item->item->name : \'No Item\' }}</span>
                    @if($item->variant)
                        <span class="badge bg-secondary ms-2">{{ $item->variant->name }}</span>
                    @endif
                    
                    @if($item->extras->count() > 0)
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-plus text-success" style="font-size: 10px;"></i> 
                            @foreach($item->extras as $e)
                                {{ $e->extra->name }} (+${{ number_format($e->price, 2) }}){{ !$loop->last ? \', \' : \'\' }}
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="text-center align-middle">${{ number_format($item->price, 2) }}</td>
                <td class="text-center align-middle"><span class="badge bg-light text-dark fs-6">{{ $item->quantity }}</span></td>
                <td class="text-end align-middle fw-bold">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="border-top-0">
            <tr><td colspan="3" class="text-end border-0 pb-0 pt-3 text-muted fw-bold">Subtotal:</td><td class="text-end border-0 pb-0 pt-3 fw-bold">${{ number_format($order->total_amount, 2) }}</td></tr>
            <tr><td colspan="3" class="text-end border-0 pb-1 pt-1 text-danger">Discount:</td><td class="text-end border-0 pb-1 pt-1 text-danger">-${{ number_format($order->discount_amount, 2) }}</td></tr>
            <tr><td colspan="3" class="text-end border-top-0 fs-4 fw-bold pb-3">Grand Total:</td><td class="text-end border-top-0 fs-4 text-success fw-bold pb-3">${{ number_format($order->grand_total, 2) }}</td></tr>
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
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: "Courier New", Courier, monospace; width: 80mm; margin: 0 auto; color: #000; background: #fff; font-size: 13px; line-height: 1.2; }
        .invoice-box { padding: 4mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #333; margin-top: 5px; padding-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #333; margin-bottom: 5px; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; text-align: left; vertical-align: top; }
        .shop-name { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 1px; }
        .shop-info { font-size: 11px; margin-bottom: 8px; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align:center; padding: 10px; background:#f1f1f1; border-bottom:1px solid #ccc; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size:16px; cursor:pointer; font-weight: bold;">PRINT RECEIPT</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size:16px; cursor:pointer;">CLOSE</button>
    </div>
    <div class="invoice-box">
        <div class="text-center border-bottom">
            <div class="shop-name">FAST FOOD HUB</div>
            <div class="shop-info">
                123 Delicious Street, City Center<br>
                Tel: +1 987 654 3210<br>
                GST/VAT: 12XYZ9876543210
            </div>
            
            <div style="font-size: 15px; margin: 5px 0; border: 1px solid #000; padding: 2px;">
                <b>{{ strtoupper($order->order_type) }}</b>
                @if($order->order_type == "Dine-in")
                    - TABLE: <b>{{ $order->table_number }}</b>
                @endif
            </div>
        </div>
        
        <div style="margin: 5px 0; font-size: 11px;">
            <b>Order:</b> {{ $order->order_number }}<br>
            <b>Date:</b> {{ $order->created_at->format("d-M-Y H:i") }}<br>
            <b>Cashier:</b> Admin
        </div>
        
        <div class="border-top border-bottom">
            <table>
                <thead>
                    <tr style="font-size: 11px;">
                        <th width="45%">Item</th>
                        <th width="15%" class="text-center">Qty</th>
                        <th width="20%">Price</th>
                        <th width="20%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td style="padding-bottom: 3px;">
                            <span class="fw-bold">{{ strtoupper($item->item ? $item->item->name : "ITEM") }}</span>
                            @if($item->variant)
                                <br><small>[{{ substr($item->variant->name, 0, 8) }}]</small>
                            @endif
                            @if($item->extras->count() > 0)
                                <div style="font-size: 9px; margin-top: 1px;">
                                @foreach($item->extras as $e)
                                    +{{ substr($e->extra->name, 0, 10) }}
                                @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
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
            @if($order->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="fw-bold fs-6">
                <td style="font-size: 16px; padding-top: 5px;">TOTAL</td>
                <td class="text-right" style="font-size: 16px; padding-top: 5px;">${{ number_format($order->grand_total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding-top: 5px; font-size: 11px;">Payment:</td>
                <td class="text-right" style="padding-top: 5px; font-size: 11px;">{{ strtoupper($order->payment_method) }} ({{ strtoupper($order->payment_status) }})</td>
            </tr>
        </table>
        
        @if($order->note)
        <div class="border-top" style="margin-top: 5px; padding-top: 5px;">
            <b>NOTE:</b> {{ $order->note }}
        </div>
        @endif
        
        <div class="text-center border-top" style="margin-top: 10px; padding-top: 10px;">
            <p style="margin: 0; font-size:12px; font-weight: bold;">THANK YOU! ENJOY YOUR MEAL</p>
            <p style="margin: 3px 0; font-size:10px;">Please visit again.</p>
        </div>
    </div>
</body>
</html>');

echo "Show and Invoice views generated.\n";

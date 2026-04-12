<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Invoice - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eeeff3; color: #333; }
        .invoice-box { max-width: 800px; margin: 20px auto; padding: 25px; border: none; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 12px; font-size: 13px; line-height: 1.5; position: relative; }
        .invoice-header { border-bottom: 2px solid #f8f9fa; padding-bottom: 12px; margin-bottom: 15px; }
        .company-logo { font-size: 24px; font-weight: bold; color: #dc3545; text-transform: uppercase; margin-bottom: 0; }
        .table thead th { background: #f8f9fa; border-bottom: none; padding: 8px 12px; }
        .table tbody td { padding: 8px 12px; }
        .total-box { background: #f8f9fa; padding: 12px; border-radius: 8px; }
        .delivery-badge { background: #dc3545; color: #fff; padding: 3px 12px; border-radius: 50px; font-size: 11px; font-weight: bold; }
        
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; margin: 0; padding: 5px; }
            .no-print { display: none !important; }
            .invoice-box { 
                width: 100%; 
                max-width: 100%; 
                margin: 0; 
                padding: 8mm 12mm 15mm 12mm; 
                border: none; 
                box-shadow: none; 
                min-height: 110mm; /* Much more compact half-page */
                overflow: visible !important;
                border-bottom: 1px dashed #bbb !important;
                border-radius: 0;
            }
            .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="container my-3 no-print">
    <div class="d-flex justify-content-between align-items-center mb-3 max-width-800 mx-auto" style="max-width: 800px;">
        <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm"><i class="fas fa-print me-2"></i> Print Invoice</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">Back</a>
    </div>
</div>

<div class="invoice-box bg-white">
    <div class="invoice-header d-flex justify-content-between align-items-center">
        <div>
            @if($tenant->logo)
                <img src="{{ asset($tenant->logo) }}" alt="Logo" style="max-height: 45px;" class="mb-1">
            @endif
            <div class="company-logo">{{ $tenant->name ?? 'FAST FOOD HUB' }}</div>
            <p class="text-muted mb-0 small" style="max-width: 400px; font-size: 11px; line-height: 1.4;">
                {{ $tenant->address ?? '' }}, {{ $tenant->city ?? '' }}, {{ $tenant->state ?? '' }} - {{ $tenant->pincode ?? '' }}<br>
                <b>Phone:</b> {{ $tenant->phone ?? '' }} @if($tenant->gst_number) | <b>GST:</b> {{ $tenant->gst_number }} @endif
            </p>
        </div>
        <div class="text-end">
            <h4 class="fw-bold mb-0 text-muted" style="letter-spacing: 2px;">INVOICE</h4>
            <p class="mb-0 small" style="font-size: 11px;"><b>#{{ $order->order_number }}</b></p>
            <p class="small text-muted mb-0" style="font-size: 10px;">{{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <div class="row mb-3 g-2">
        <div class="col-6">
            <div class="p-2 rounded-2 bg-light border h-100" style="font-size: 11px;">
                <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 9px;">BILL TO:</h6>
                <p class="mb-0 fw-bold text-dark">{{ $order->customer->name ?? 'Guest Customer' }}</p>
                <p class="mb-0 text-muted">{{ $order->customer->phone }}</p>
            </div>
        </div>
        <div class="col-6">
            <div class="p-2 rounded-2 bg-light border h-100" style="font-size: 11px;">
                <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 9px;">SHIP TO:</h6>
                <p class="mb-0 text-dark fw-medium" style="white-space: pre-line; line-height: 1.3;">{{ $order->delivery_address ?: 'No Address Provided' }}</p>
            </div>
        </div>
    </div>

    <table class="table table-sm mb-3">
        <thead>
            <tr class="small text-uppercase text-muted" style="font-size: 10px;">
                <th>Description</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td style="padding-bottom: 5px;">
                    <div class="fw-bold text-dark">{{ $item->item->name ?? 'Item' }}</div>
                    <div class="small text-muted" style="font-size: 10px;">
                        @if($item->variant) {{ $item->variant->name }} @endif
                        @if($item->extras->count() > 0) | Extras: {{ $item->extras->map(fn($e) => $e->extra->name)->join(', ') }} @endif
                    </div>
                </td>
                <td class="text-center align-middle">{{ $item->quantity }}</td>
                <td class="text-end align-middle">₹{{ number_format($item->price, 2) }}</td>
                <td class="text-end align-middle fw-bold">₹{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row align-items-end">
        <div class="col-7">
            <div class="d-flex flex-column gap-1">
                <div class="d-inline-block">
                    <span class="badge bg-danger rounded-pill px-3 py-1 text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home Delivery</span>
                </div>
                <div class="text-muted" style="font-size: 11px;">
                    <i class="fas fa-credit-card me-1"></i> <b>Payment:</b> {{ $order->payment_method }} ({{ $order->payment_status }})
                </div>
                @if($order->note)
                <div class="mt-2 p-2 bg-light rounded border-start border-3 border-danger" style="font-size: 10px; max-width: 90%;">
                    <i class="fas fa-sticky-note me-1 text-muted"></i> <b>Note:</b> {{ $order->note }}
                </div>
                @endif
            </div>
        </div>
        <div class="col-5">
            <div class="total-box">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Subtotal</span>
                    <span class="small">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="d-flex justify-content-between mb-1 text-danger small">
                    <span>Discount</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between pt-1 border-top border-secondary">
                    <span class="fw-bold">Grand Total</span>
                    <span class="fw-bold text-danger">₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 pt-2 text-center text-muted small border-top">
        <p class="mb-0" style="font-size: 10px;">This is a computer-generated invoice. No signature required. Thank you for ordering with {{ $tenant->name ?? 'Fast Food Hub' }}!</p>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>

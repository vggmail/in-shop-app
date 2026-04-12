<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Invoice - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .05); font-size: 14px; line-height: 24px; }
        .invoice-header { border-bottom: 2px solid #f8f9fa; padding-bottom: 20px; margin-bottom: 20px; }
        .company-logo { font-size: 28px; font-weight: bold; color: #dc3545; text-transform: uppercase; }
        .table thead th { background: #f8f9fa; border-bottom: none; }
        .total-box { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .delivery-badge { background: #dc3545; color: #fff; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            .invoice-box { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="container my-5 no-print">
    <div class="d-flex justify-content-between align-items-center mb-3 max-width-800 mx-auto" style="max-width: 800px;">
        <button onclick="window.print()" class="btn btn-dark rounded-pill px-4"><i class="fas fa-print me-2"></i> Print Invoice</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Dashboard</a>
    </div>
</div>

<div class="invoice-box bg-white">
    <div class="invoice-header d-flex justify-content-between align-items-start">
        <div>
            @if($tenant->logo)
                <img src="{{ asset($tenant->logo) }}" alt="Logo" style="max-height: 60px;" class="mb-2">
            @endif
            <div class="company-logo">{{ $tenant->name ?? 'FAST FOOD HUB' }}</div>
            <p class="text-muted mb-0 small" style="max-width: 300px;">
                {{ $tenant->address ?? '' }}<br>
                {{ $tenant->city ?? '' }}, {{ $tenant->state ?? '' }} - {{ $tenant->pincode ?? '' }}<br>
                <b>Phone:</b> {{ $tenant->phone ?? '' }}<br>
                @if($tenant->gst_number) <b>GST:</b> {{ $tenant->gst_number }} @endif
            </p>
        </div>
        <div class="text-end">
            <h2 class="fw-light mb-1">INVOICE</h2>
            <div class="delivery-badge mb-2">HOME DELIVERY</div>
            <p class="mb-0 small"><b>Order ID:</b> #{{ $order->order_number }}</p>
            <p class="small text-muted"><b>Date:</b> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Customer Details</h6>
            <p class="mb-0"><strong>{{ $order->customer->name ?? 'Guest Customer' }}</strong></p>
            <p class="mb-0 text-muted">{{ $order->customer->phone }}</p>
            @if($order->customer->email)
                <p class="mb-0 text-muted small">{{ $order->customer->email }}</p>
            @endif
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Delivery Address</h6>
            <p class="mb-0 small" style="white-space: pre-line;">{{ $order->delivery_address ?: 'No Address Provided' }}</p>
        </div>
    </div>

    <table class="table mb-4">
        <thead>
            <tr>
                <th>Item Description</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div class="fw-bold">{{ $item->item->name ?? 'Item' }}</div>
                    @if($item->variant)
                        <div class="small text-muted">Size: {{ $item->variant->name }}</div>
                    @endif
                    @if($item->extras->count() > 0)
                        <div class="small text-muted italic">Extras: {{ $item->extras->map(fn($e) => $e->extra->name)->join(', ') }}</div>
                    @endif
                </td>
                <td class="text-center align-middle">{{ $item->quantity }}</td>
                <td class="text-end align-middle">₹{{ number_format($item->price, 2) }}</td>
                <td class="text-end align-middle fw-bold">₹{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-5">
            <div class="total-box">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span>Discount</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between pt-2 border-top border-dark">
                    <span class="fw-bold">Grand Total</span>
                    <span class="fw-bold h5 mb-0 text-danger">₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
            <div class="mt-3 text-end">
                <p class="small mb-0"><b>Payment:</b> {{ $order->payment_method }} ({{ $order->payment_status }})</p>
            </div>
        </div>
    </div>

    @if($order->note)
    <div class="mt-5 p-3 bg-light rounded-3">
        <h6 class="small fw-bold text-muted text-uppercase mb-1">Customer Note:</h6>
        <p class="small mb-0 fst-italic">"{{ $order->note }}"</p>
    </div>
    @endif

    <div class="mt-5 pt-5 text-center text-muted small border-top">
        <p>This is a computer-generated invoice. No signature required.</p>
        <p class="mb-0 fw-bold">Thank you for ordering with {{ $tenant->company_name ?? 'Fast Food Hub' }}!</p>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>

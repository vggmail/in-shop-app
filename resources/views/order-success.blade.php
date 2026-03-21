<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($order->payment_status == 'Pending') ? 'Complete Payment' : 'Order Placed Successfully' }} | Fast Food Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { font-family: "Outfit", sans-serif; background-color: #f8f9fa; color: var(--dark); padding: 50px 15px; }
        .success-box { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto; overflow: hidden; }
        .success-header { background: #10ac84; color: white; padding: 40px 20px; text-align: center; }
        .check-icon { font-size: 60px; margin-bottom: 15px; animation: scaleIn 0.5s ease; }
        @keyframes scaleIn { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        .dashed-line { border-top: 2px dashed #eee; margin: 20px 0; }
        .item-list { font-size: 14px; margin-bottom: 15px; border-bottom: 1px solid #f1f2f6; padding-bottom: 10px; }
        .item-list:last-child { border-bottom: none; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .btn, a, .action-btns { display: none !important; }
            .success-box { box-shadow: none !important; max-width: 100% !important; border-radius: 0 !important; margin: 0 !important; }
        }
    </style>
</head>
<body>

    <div class="success-box">
        <div class="success-header {{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'bg-primary' : '' }}" style="{{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'background: #3498db;' : '' }}">
            <i class="fas {{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'fa-wallet' : 'fa-check-circle' }} check-icon"></i>
            <h2 class="fw-bold mb-1">{{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'Complete Payment' : 'Order Received!' }}</h2>
            <p class="mb-0 opacity-75">{{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'Click below to pay via any UPI app.' : 'Your order is being processed.' }}</p>
        </div>
        
        <div class="p-4">
            <h5 class="fw-bold text-center mb-4">Order #{{ $order->order_number }}</h5>
            
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Customer</span>
                <span class="text-dark fw-bold">{{ $order->customer->name ?? 'Guest' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Phone</span>
                <span class="text-dark fw-bold">{{ $order->customer->phone ?? 'N/A' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Type</span>
                <span class="text-dark fw-bold">
                    {{ $order->order_type }}
                    @if($order->table_number) <span class="badge bg-danger ms-1">Table {{ $order->table_number }}</span> @endif
                </span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Payment</span>
                <span class="text-dark fw-bold">{{ $order->payment_method }} • 
                    <span class="{{ $order->payment_status == 'Paid' ? 'text-success' : 'text-warning' }}">{{ $order->payment_status }}</span>
                </span>
            </div>

            <div class="dashed-line"></div>
            
            @if($order->payment_method == 'UPI' && $order->payment_status == 'Pending')
            <div class="bg-light p-4 rounded-4 text-center mb-4 border border-primary border-opacity-25 payment-preview-box">
                <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-qrcode text-primary me-2"></i>Complete your UPI Payment</h6>
                
                @php
                    $upiId = $tenant->upi_id ?? 'admin@upi';
                    $upiName = urlencode($tenant->name ?? 'Fast Food Hub');
                    $upiUrl = "upi://pay?pa=" . $upiId . "&pn=" . $upiName . "&am=" . $order->grand_total . "&tr=" . $order->order_number . "&cu=INR";
                @endphp

                <!-- Only visible on mobile devices -->
                <div class="d-md-none">
                    <a href="{{ $upiUrl }}" class="btn btn-primary btn-lg w-100 rounded-pill mb-3 fw-bold py-3 shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i> OPEN UPI APP TO PAY
                    </a>
                    <div class="text-muted small mb-3">Pay via any installed UPI app.</div>
                </div>

                <!-- Visible on desktop (Web) -->
                <div class="d-none d-md-block mb-3">
                    <p class="small text-muted mb-3">Please scan the QR code using any UPI app (Google Pay, PhonePe, Paytm, etc.) to complete your payment of <strong>₹{{ number_format($order->grand_total, 2) }}</strong>.</p>
                </div>
                
                <div class="d-inline-block bg-white p-2 rounded shadow-sm mb-3" style="width: 180px; height: 180px;" id="qrcode"></div>
                
                <div class="dashed-line mb-3"></div>

                <div class="bg-white p-3 rounded-3 border">
                   <div class="small fw-bold text-muted text-uppercase mb-1">Store UPI ID</div>
                   <div class="d-flex justify-content-between align-items-center">
                       <code class="fs-6 text-dark" id="upi_id_text">{{ $upiId }}</code>
                       <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="copyUPI()">COPY</button>
                   </div>
                </div>
            </div>
            @endif

            <h6 class="fw-bold mb-3 small text-muted text-uppercase">Items Ordered</h6>
            
            @foreach($order->items as $orderItem)
                <div class="item-list d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">{{ $orderItem->quantity }}x {{ $orderItem->item->name ?? 'Food Item' }}</div>
                        @if($orderItem->variant)
                            <div class="small text-muted mb-1"><i class="fas fa-caret-right me-1"></i>{{ $orderItem->variant->name }}</div>
                        @endif
                        @if($orderItem->extras->count() > 0)
                            <div class="small text-success"><i class="fas fa-plus me-1"></i>
                                @foreach($orderItem->extras as $ext)
                                    {{ $ext->extra->name ?? 'Extra' }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="fw-bold text-end">₹{{ number_format($orderItem->total, 2) }}</div>
                </div>
            @endforeach
            
            <div class="dashed-line"></div>

            <div class="d-flex justify-content-between mb-1 text-muted">
                <span>Subtotal</span>
                <span>₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="d-flex justify-content-between mb-1 text-danger">
                <span>Discount</span>
                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="d-flex justify-content-between mt-3 bg-light p-3 rounded-3">
                <h5 class="fw-bold mb-0">{{ ($order->payment_method == 'UPI' && $order->payment_status == 'Pending') ? 'Final Amount' : 'Total Paid' }}</h5>
                <h5 class="fw-bold text-success mb-0">₹{{ number_format($order->grand_total, 2) }}</h5>
            </div>

            <div class="mt-3 action-btns d-flex gap-2 justify-content-center">
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill py-2 flex-grow-1 fw-bold"><i class="fas fa-file-pdf me-2"></i> SAVE PDF</button>
                <button onclick="saveImage()" class="btn btn-outline-dark rounded-pill py-2 flex-grow-1 fw-bold"><i class="fas fa-image me-2"></i> SAVE IMAGE</button>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('home') }}" class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow-sm w-100"><i class="fas fa-reply me-2"></i> PLACE ANOTHER ORDER</a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if($order->payment_method == 'UPI' && $order->payment_status == 'Pending')
                let upiString = "{{ $upiUrl ?? '' }}";
                new QRCode(document.getElementById("qrcode"), {
                    text: upiString,
                    width: 180,
                    height: 180,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });

                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    // Start auto-direct timer or direct immediately
                    console.log("Redirecting to UPI on mobile...");
                    setTimeout(() => {
                        window.location.href = upiString;
                    }, 1000);
                }
            @endif
        });

        function copyUPI() {
            let upiId = document.getElementById("upi_id_text").innerText;
            navigator.clipboard.writeText(upiId).then(() => {
                alert("UPI ID Copied: " + upiId);
            });
        }

        function saveImage() {
            const el = document.querySelector(".success-box");
            const btns = document.querySelector(".action-btns");
            const backBtn = document.querySelector(".btn-dark");
            
            // Hide buttons briefly for clean image
            btns.style.display = 'none';
            backBtn.style.display = 'none';
            
            html2canvas(el, { scale: 2, backgroundColor: "#ffffff" }).then(canvas => {
                let link = document.createElement('a');
                link.download = 'Receipt-{{ $order->order_number }}.png';
                link.href = canvas.toDataURL("image/png");
                link.click();
                
                // Show buttons again
                btns.style.display = 'flex';
                backBtn.style.display = 'inline-block';
            });
        }
    </script>
</body>
</html>

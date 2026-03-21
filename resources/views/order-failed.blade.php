<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Failed | Fast Food Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ff4757; --dark: #2f3542; --light: #f1f2f6; }
        body { font-family: "Outfit", sans-serif; background-color: #f8f9fa; color: var(--dark); padding: 50px 15px; }
        .error-box { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto; overflow: hidden; }
        .error-header { background: #ff4757; color: white; padding: 40px 20px; text-align: center; }
        .error-icon { font-size: 60px; margin-bottom: 15px; }
        .dashed-line { border-top: 2px dashed #eee; margin: 20px 0; }
    </style>
</head>
<body>

    <div class="error-box">
        <div class="error-header">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h2 class="fw-bold mb-1">Order Failed!</h2>
            <p class="mb-0 opacity-75">Something went wrong while placing your order.</p>
        </div>
        
        <div class="p-4 text-center">
            <div class="mb-4">
                <h5 class="fw-bold text-danger">Wait, don't give up!</h5>
                <p class="text-muted small">Your cart items are still saved. You can try placing the order again or choose a different payment method.</p>
            </div>

            <div class="alert alert-danger font-monospace small">
                Error: {{ $message ?? 'Unknown session error or stock issue.' }}
            </div>

            <div class="dashed-line"></div>

            <div class="mt-4">
                <a href="{{ url('/') }}" class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow-sm w-100">
                    <i class="fas fa-redo me-2"></i> TRY AGAIN
                </a>
                <p class="mt-3 small text-muted">Need help? Call us at <strong>{{ $tenant->phone ?? 'Support' }}</strong></p>
            </div>
        </div>
    </div>

</body>
</html>

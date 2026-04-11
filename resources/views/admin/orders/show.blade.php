@extends("layouts.admin")
@section("content")
    <div class="card p-4 shadow border-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="fas fa-receipt text-muted me-2"></i> Order: {{ $order->order_number }}</h2>
            <div class="d-flex gap-2">
                @if($tenant->whatsapp_number && $order->customer)
                    @php
                        $message = "Hello " . $order->customer->name . "! Your order #" . $order->order_number . " from " . $tenant->name . " for ₹" . number_format($order->grand_total, 2) . " is now " . $order->status . ".";
                        $wa_link = "https://wa.me/" . ($order->customer->phone ?? '') . "?text=" . urlencode($message);
                    @endphp
                    <a href="{{ $wa_link }}" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp"></i> Send on WhatsApp</a>
                @endif
                <a href="{{ route("orders.invoice", $order->id) }}" target="_blank" class="btn btn-dark"><i
                        class="fas fa-print"></i> Print Receipt</a>
            </div>
        </div>

        <div class="row mb-4 bg-light p-3 rounded">
            <div class="col-md-4 border-end">
                <h5 class="fw-bold mb-3 text-secondary">Dining Details</h5>
                <p class="mb-1"><strong>Type:</strong>
                    @if($order->order_type == "Takeaway")
                        <span class="badge bg-secondary py-1 px-3"><i class="fas fa-shopping-bag"></i> Takeaway</span>
                    @elseif($order->order_type == "Home Delivery" || $order->order_type == "Delivery")
                        <span class="badge bg-danger py-1 px-3 shadow-sm"><i class="fas fa-motorcycle"></i> Home Delivery</span>
                    @else
                        <span class="badge bg-primary py-1 px-3"><i class="fas fa-chair"></i> Dine-In</span> (Table:
                        <strong>{{ $order->table_number }}</strong>)
                    @endif
                </p>
                @if(($order->order_type == "Home Delivery" || $order->order_type == "Delivery") && $order->delivery_address)
                    <div class="mt-3 p-2 border border-danger border-opacity-25 rounded bg-white">
                        <p class="mb-1 small fw-bold text-danger"><i class="fas fa-map-marker-alt"></i> DELIVERY ADDRESS:</p>
                        <p class="mb-0 small fw-bold">{{ $order->delivery_address }}</p>
                    </div>
                @endif
                <p class="mb-0"><strong>Status:</strong>
                <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST"
                    class="d-inline-flex align-items-center mt-2">
                    @csrf
                    <select name="status" class="form-select form-select-sm me-2 bg-info text-dark fw-bold"
                        onchange="this.form.submit()">
                        <option value="Preparing" {{ $order->status == 'Preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="Ready" {{ $order->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                        <option value="Completed" {{ $order->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </form>
                </p>
            </div>
            <div class="col-md-4 border-end ps-4">
                <h5 class="fw-bold mb-3 text-secondary">Payment Info</h5>
                <p class="mb-1"><strong>Time:</strong> <i class="far fa-clock"></i>
                    {{ $order->created_at->format('Y-m-d H:i A') }}</p>
                <p class="mb-1"><strong>Method:</strong> {{ $order->payment_method }}</p>
                <p class="mb-0"><strong>Status:</strong>
                <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST"
                    class="d-inline-flex align-items-center mt-2">
                    @csrf
                    <select name="payment_status"
                        class="form-select form-select-sm {{ $order->payment_status == 'Paid' ? 'bg-success' : 'bg-danger' }} text-white fw-bold"
                        onchange="this.form.submit()">
                        <option value="Paid" {{ $order->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Pending" {{ $order->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </form>
                </p>
            </div>
            <div class="col-md-4 ps-4">
                <h5 class="fw-bold mb-3 text-secondary">Order Note</h5>
                <div class="p-2 border rounded bg-white text-danger fw-bold fst-italic shadow-sm" style="min-height: 60px;">
                    {{ $order->note ? "ðŸ”Š " . $order->note : "No special instructions" }}
                </div>
            </div>
        </div>

        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Item Description</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-bold fs-6">{{ $item->item ? $item->item->name : 'No Item' }}
                                {{ ($item->item && $item->item->default_size && !$item->variant) ? '- ' . $item->item->default_size : '' }}</span>
                            @if($item->variant)
                                <span class="badge bg-secondary ms-2">{{ $item->variant->name ?? 'Default' }}</span>
                            @endif

                            @if($item->extras->count() > 0)
                                <div class="mt-1 small text-muted">
                                    <i class="fas fa-plus text-success" style="font-size: 10px;"></i>
                                    @foreach($item->extras as $e)
                                        {{ $e->extra->name ?? 'Extra' }}
                                        (+₹{{ number_format($e->price, 2) }}){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center align-middle">₹{{ number_format($item->price, 2) }}</td>
                        <td class="text-center align-middle"><span
                                class="badge bg-light text-dark fs-6">{{ $item->quantity }}</span></td>
                        <td class="text-end align-middle fw-bold">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-top-0">
                <tr>
                    <td colspan="3" class="text-end border-0 pb-0 pt-3 text-muted fw-bold">Subtotal:</td>
                    <td class="text-end border-0 pb-0 pt-3 fw-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end border-0 pb-1 pt-1 text-danger">Discount:</td>
                    <td class="text-end border-0 pb-1 pt-1 text-danger">-₹{{ number_format($order->discount_amount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end border-top-0 fs-4 fw-bold pb-3">Grand Total:</td>
                    <td class="text-end border-top-0 fs-4 text-success fw-bold pb-3">
                        ₹{{ number_format($order->grand_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
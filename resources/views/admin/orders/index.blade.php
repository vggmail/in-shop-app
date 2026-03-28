@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Kitchen / Order Queue</h2>
</div>
<div class="card bg-white p-3 shadow-sm border-0">
    <table class="table table-hover">
        <thead class="table-light"><tr><th>Order #</th><th>Type/Table</th><th>Note</th><th>Total</th><th>Status</th><th>Payment</th><th>Time</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($orders as $o)
            <tr>
                <td class="fw-bold">{{ $o->order_number }}</td>
                <td>
                    @if($o->order_type == "Takeaway")
                        <span class="badge bg-secondary"><i class="fas fa-shopping-bag"></i> Takeaway</span>
                    @else
                        <span class="badge bg-primary"><i class="fas fa-chair"></i> Dine-In</span>
                        <div class="small fw-bold text-muted">{{ $o->table_number }}</div>
                    @endif
                </td>
                <td style="max-width: 150px;" class="small text-danger">{{ $o->note }}</td>
                <td class="font-weight-bold text-success">&#8377;{{ $o->grand_total }}</td>
                <td>
                    <form action="{{ route('orders.updateStatus', $o->id) }}" method="POST">
                        @csrf
                        <select name="status" class="form-select form-select-sm fw-bold {{ $o->status == 'Preparing' ? 'bg-warning text-dark' : ($o->status == 'Ready' ? 'bg-info text-white' : 'bg-success text-white') }}" onchange="this.form.submit()">
                            <option value="Preparing" {{ $o->status == 'Preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="Ready" {{ $o->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                            <option value="Completed" {{ $o->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </form>
                </td>
                <td>
                    <form action="{{ route('orders.updateStatus', $o->id) }}" method="POST">
                        @csrf
                        <select name="payment_status" class="form-select form-select-sm fw-bold mt-1 {{ $o->payment_status == 'Pending' ? 'bg-danger text-white' : 'bg-success text-white' }}" onchange="this.form.submit()">
                            <option value="Pending" {{ $o->payment_status == 'Pending' ? 'selected' : '' }}>{{ $o->payment_method }} - Pending</option>
                            <option value="Paid" {{ $o->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </form>
                </td>
                <td class="small">{{ $o->created_at->diffForHumans() }}<br><span class="text-muted">{{ $o->created_at->format("H:i A") }}</span></td>
                <td>
                    <a href="{{ route('orders.show', $o->id) }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i> View</a>
                    <a target="_blank" href="{{ route('orders.invoice', $o->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-print"></i> Receipt</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links("pagination::bootstrap-5") }}
</div>
@endsection


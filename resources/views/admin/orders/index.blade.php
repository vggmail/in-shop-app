@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Kitchen / Order Queue</h2>
</div>
<div class="card bg-white p-3 shadow-sm border-0">
    <table class="table table-hover">
        <thead class="table-light"><tr><th>Order #</th><th>Type/Table</th><th>Note</th><th>Total</th><th>Status</th><th>Time</th><th>Action</th></tr></thead>
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
                <td class="font-weight-bold text-success">${{ $o->grand_total }}</td>
                <td>
                    @if($o->status == "Preparing")
                        <span class="badge bg-warning text-dark"><i class="fas fa-fire"></i> Preparing</span>
                    @elseif($o->status == "Ready")
                        <span class="badge bg-info"><i class="fas fa-bell"></i> Ready</span>
                    @else
                        <span class="badge bg-success"><i class="fas fa-check-double"></i> Completed</span>
                    @endif
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
@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Shift History</h2>
    @php $activeShift = \App\Models\Shift::where('status', 'Open')->first(); @endphp
    
    <div class="d-flex gap-2">
        @if($activeShift)
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#pettyCashModal">
                <i class="fas fa-money-bill-alt me-2"></i> Petty Cash
            </button>
            <a href="{{ route('shifts.close.form') }}" class="btn btn-danger">
                <i class="fas fa-stop-circle me-2"></i> Close Shift
            </a>
        @else
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                <i class="fas fa-play-circle me-2"></i> Open Shift
            </button>
        @endif
    </div>
</div>

@if($activeShift)
<div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="fas fa-info-circle me-2"></i> 
        <strong>Current Shift is Active:</strong> Started by {{ $activeShift->user->name }} at {{ $activeShift->opened_at->format('d M, h:i A') }}
    </div>
    <div class="fw-bold h5 mb-0">Opening Balance: ₹{{ number_format($activeShift->opening_balance, 2) }}</div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Opened At</th>
                        <th>Closed At</th>
                        <th>Staff</th>
                        <th>Opening Bal.</th>
                        <th>Closing Bal.</th>
                        <th>Expected Bal.</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                    <tr>
                        <td>{{ $shift->opened_at->format('d M, h:i A') }}</td>
                        <td>{{ $shift->closed_at ? $shift->closed_at->format('d M, h:i A') : '-' }}</td>
                        <td>{{ $shift->user->name }}</td>
                        <td>₹{{ number_format($shift->opening_balance, 2) }}</td>
                        <td>{{ $shift->closing_balance ? '₹'.number_format($shift->closing_balance, 2) : '-' }}</td>
                        <td>{{ $shift->expected_balance ? '₹'.number_format($shift->expected_balance, 2) : '-' }}</td>
                        <td>
                            @if($shift->status == 'Closed')
                                @php $diff = $shift->closing_balance - $shift->expected_balance; @endphp
                                @if($diff == 0)
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i> Balanced</span>
                                @elseif($diff > 0)
                                    <span class="text-primary">+₹{{ number_format($diff, 2) }} (Excess)</span>
                                @else
                                    <span class="text-danger">₹{{ number_format($diff, 2) }} (Shortage)</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $shift->status == 'Open' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $shift->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $shifts->links() }}
        </div>
    </div>
</div>

<!-- Open Shift Modal -->
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('shifts.open') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Open New Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-4">
                    <div class="bg-success-soft text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-cash-register fa-2x"></i>
                    </div>
                    <p class="text-muted small px-4">Enter the starting cash amount currently in the drawer to begin the shift.</p>
                </div>
                <div class="mb-3 px-4">
                    <label class="form-label small fw-bold text-muted">Opening Cash Balance (₹)</label>
                    <div class="input-group input-group-lg border rounded-pill overflow-hidden">
                        <span class="input-group-text bg-white border-0 ps-3">₹</span>
                        <input type="number" step="0.01" name="opening_balance" class="form-control border-0 shadow-none" required value="0" autofocus>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold">START SHIFT</button>
            </div>
        </form>
    </div>
</div>

<!-- Petty Cash Modal -->
<div class="modal fade" id="pettyCashModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('shifts.petty_cash') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Record Petty Cash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 px-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Transaction Type</label>
                    <div class="d-flex gap-3">
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="type" id="type_out" value="Out" checked>
                            <label class="btn btn-outline-danger w-100 py-2 rounded-3" for="type_out">
                                <i class="fas fa-minus-circle me-1"></i> Cash Out
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="type" id="type_in" value="In">
                            <label class="btn btn-outline-success w-100 py-2 rounded-3" for="type_in">
                                <i class="fas fa-plus-circle me-1"></i> Cash In
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control rounded-3" required placeholder="0.00">
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted">Reason / Note</label>
                    <textarea name="reason" class="form-control rounded-3" rows="2" required placeholder="e.g. Milk purchase, Cleaning tips"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold">SAVE LOG</button>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-success-soft { background-color: #ecfdf5; }
    .bg-info-soft { background-color: #eff6ff; }
</style>
@endsection

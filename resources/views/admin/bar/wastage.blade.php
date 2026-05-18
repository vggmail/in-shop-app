@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <h2 class="mb-0 text-dark">Bar Management Console</h2>
    <p class="text-muted">Track bar inventory, log breakage & spillages, and manage Happy Hour schedules.</p>
</div>

<!-- Tabs to separate Wastage and Happy Hours -->
<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills bg-white p-2 rounded shadow-sm gap-2" id="barTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-2 fw-bold text-uppercase small" id="wastage-tab" data-bs-toggle="tab" data-bs-target="#wastage" type="button" role="tab">
                    ⚠️ Stock Wastage & Breakage
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 fw-bold text-uppercase small" id="happyhours-tab" data-bs-toggle="tab" data-bs-target="#happyhours" type="button" role="tab">
                    ⏰ Happy Hours Pricing
                </button>
            </li>
            <li class="nav-item ms-auto">
                <a href="{{ route('bar.excise') }}" class="btn btn-outline-primary fw-bold text-uppercase small px-4 py-2">
                    📊 Excise & Consumption Reports
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content" id="barTabsContent">
    
    <!-- Tab 1: Wastage & Breakage -->
    <div class="tab-pane fade show active" id="wastage" role="tabpanel">
        <div class="row">
            <!-- Add Wastage Form (Left Column) -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Log Wastage / Spills</h5>
                    </div>
                    <form action="{{ route('bar.wastage.store') }}" method="POST" class="card-body">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Select Liquor / Drink</label>
                            <select name="ingredient_id" id="wastage_ingredient" class="form-select" required onchange="updateWastageHelper()">
                                <option value="">Select Item</option>
                                @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" data-size="{{ $ing->bottle_size_ml ?? '750' }}">{{ $ing->name }} ({{ $ing->bottle_size_ml }}ml Bottle)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Wastage Type</label>
                            <select name="type" class="form-select" required>
                                <option value="Breakage">🍾 Bottle Breakage</option>
                                <option value="Spill">💧 Spillage / Leakage</option>
                                <option value="Free Pour">🍷 Free Pour (Over-pour)</option>
                                <option value="Complimentary">🎁 Complimentary / Manager Drink</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Volume Spilled</label>
                            <select name="measure_type" id="wastage_measure" class="form-select" required onchange="toggleCustomMl()">
                                <option value="peg30">30 ml Peg</option>
                                <option value="peg60">60 ml Peg</option>
                                <option value="peg90">90 ml Peg</option>
                                <option value="bottle">1 Full Bottle</option>
                                <option value="custom_ml">Custom Volume (ml)</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="custom_ml_group">
                            <label class="form-label fw-semibold text-dark">Custom Volume (ml)</label>
                            <input type="number" step="1" name="custom_ml" class="form-control" placeholder="e.g. 150">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Notes / Reason</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Explain the breakage or spill..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 text-dark">
                            <i class="fas fa-check-circle me-1"></i> Log and Deduct Stock
                        </button>
                    </form>
                </div>
            </div>

            <!-- Wastage Log Table (Right Column) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ul text-primary me-2"></i>Wastage History Log</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Liquor Item</th>
                                        <th>Type</th>
                                        <th>Volume Lost</th>
                                        <th>Fraction Deducted</th>
                                        <th>Logged By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($wastages as $w)
                                    <tr>
                                        <td class="small">{{ $w->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $w->ingredient->name ?? 'Deleted Ingredient' }}</div>
                                        </td>
                                        <td>
                                            @if($w->type == 'Breakage')
                                                <span class="badge bg-danger text-white"><i class="fas fa-wine-bottle me-1"></i>Breakage</span>
                                            @elseif($w->type == 'Spill')
                                                <span class="badge bg-warning text-dark"><i class="fas fa-tint me-1"></i>Spill</span>
                                            @elseif($w->type == 'Free Pour')
                                                <span class="badge bg-info text-dark"><i class="fas fa-glass-martini-alt me-1"></i>Free Pour</span>
                                            @else
                                                <span class="badge bg-success text-white"><i class="fas fa-gift me-1"></i>Complimentary</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">{{ $w->volume_ml }} ml</td>
                                        <td class="small text-danger fw-bold">-{{ number_format($w->quantity, 3) }} units</td>
                                        <td class="small">{{ $w->user->name ?? 'System' }}</td>
                                        <td class="small text-muted">{{ $w->notes ?: '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-glass-whiskey fa-3x mb-3 text-light"></i>
                                            <p class="mb-0">No bar wastage logged yet.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($wastages->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $wastages->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Happy Hours Rules -->
    <div class="tab-pane fade" id="happyhours" role="tabpanel">
        <div class="row">
            <!-- Add Happy Hour Form -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus text-primary me-2"></i>Create Happy Hour Rule</h5>
                    </div>
                    <form action="{{ route('bar.happy_hours.store') }}" method="POST" class="card-body">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Schedule Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Weekend Double Happy Hour" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Discount Percentage (%)</label>
                            <input type="number" step="1" min="1" max="100" name="discount_percent" class="form-control" placeholder="e.g. 15" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold text-dark">Start Time</label>
                                <input type="time" name="start_time" class="form-control" required value="17:00">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold text-dark">End Time</label>
                                <input type="time" name="end_time" class="form-control" required value="20:00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Active Days</label>
                            <div class="row">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="day_{{ $day }}" checked>
                                        <label class="form-check-label text-dark" for="day_{{ $day }}">{{ $day }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                            <i class="fas fa-save me-1"></i> Save Happy Hour Schedule
                        </button>
                    </form>
                </div>
            </div>

            <!-- Happy Hours Rules List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clock text-info me-2"></i>Configured Happy Hour Promotions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Promo Name</th>
                                        <th>Discount</th>
                                        <th>Timeslot</th>
                                        <th>Schedule Days</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($happyHours as $hh)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $hh->name }}</td>
                                        <td><span class="badge bg-success fs-6">{{ floatval($hh->discount_percent) }}% OFF</span></td>
                                        <td>{{ date('h:i A', strtotime($hh->start_time)) }} - {{ date('h:i A', strtotime($hh->end_time)) }}</td>
                                        <td class="small">{{ str_replace(',', ', ', $hh->days_of_week) }}</td>
                                        <td>
                                            @if($hh->isActiveNow())
                                                <span class="badge bg-danger pulse-dot"><i class="fas fa-play me-1"></i>Active Now</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fas fa-pause me-1"></i>Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('bar.happy_hours.toggle', $hh->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border text-dark fw-bold">
                                                    @if($hh->is_active)
                                                        <i class="fas fa-toggle-on text-success me-1"></i> Enabled
                                                    @else
                                                        <i class="fas fa-toggle-off text-muted me-1"></i> Disabled
                                                    @endif
                                                </button>
                                            </form>
                                            <form action="{{ route('bar.happy_hours.destroy', $hh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Happy Hour Promo?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border ms-1">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-clock fa-3x mb-3 text-light"></i>
                                            <p class="mb-0">No active Happy Hour rules configured.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleCustomMl() {
        const val = document.getElementById('wastage_measure').value;
        const customGroup = document.getElementById('custom_ml_group');
        if (val === 'custom_ml') {
            customGroup.classList.remove('d-none');
            customGroup.querySelector('input').setAttribute('required', 'required');
        } else {
            customGroup.classList.add('d-none');
            customGroup.querySelector('input').removeAttribute('required');
        }
    }

    function updateWastageHelper() {
        // Can be used if we need to load dynamic information when changing the select
    }
</script>
<style>
    .pulse-dot {
        position: relative;
        animation: pulseAnim 1.8s infinite;
    }
    @keyframes pulseAnim {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>
@endsection

@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Ingredient Master</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i> Add Ingredient
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Unit</th>
                        <th>Current Stock</th>
                        <th>Min Level</th>
                        <th>Cost/Unit</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ing)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                {{ $ing->name }}
                                @if($ing->is_alcohol)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-wine-bottle me-1"></i>Alcohol ({{ $ing->bottle_size_ml }} ml)</span>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ strtoupper($ing->unit) }}</span></td>
                        <td>
                            <span class="fw-bold {{ $ing->stock_quantity <= $ing->min_stock_level ? 'text-danger' : 'text-success' }}">
                                {{ number_format($ing->stock_quantity, 2) }}
                            </span>
                        </td>
                        <td>{{ number_format($ing->min_stock_level, 2) }}</td>
                        <td>₹{{ number_format($ing->cost_per_unit, 2) }}</td>
                        <td>
                            @if($ing->stock_quantity <= $ing->min_stock_level)
                                <span class="badge bg-danger">Low Stock</span>
                            @else
                                <span class="badge bg-success">Healthy</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border" onclick="editIng({{ json_encode($ing) }})">
                                <i class="fas fa-edit text-primary"></i>
                            </button>
                            <form action="{{ route('ingredients.destroy', $ing->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border" onclick="return confirm('Delete this ingredient?')">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('ingredients.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ingredient Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Tomato, Chicken Breast">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select" required>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="gm">Gram (gm)</option>
                            <option value="ltr">Liter (ltr)</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="pkt">Packet (pkt)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Per Unit (₹)</label>
                        <input type="number" step="0.01" name="cost_per_unit" class="form-control" required value="0">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" step="0.001" name="stock_quantity" class="form-control" required value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Stock Level (Alert)</label>
                        <input type="number" step="0.001" name="min_stock_level" class="form-control" required value="1">
                    </div>
                </div>
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_alcohol" id="add_is_alcohol" value="1" onchange="toggleAddBottleSize()">
                        <label class="form-check-label fw-bold" for="add_is_alcohol">🍾 Is Alcoholic Liquor / Bar Item</label>
                    </div>
                    <div class="mb-0 d-none" id="add_bottle_size_group">
                        <label class="form-label">Bottle Size (ml)</label>
                        <select name="bottle_size_ml" class="form-select">
                            <option value="750">750 ml (Standard)</option>
                            <option value="1000">1000 ml (1 Liter)</option>
                            <option value="375">375 ml (Half)</option>
                            <option value="180">180 ml (Quarter)</option>
                            <option value="500">500 ml</option>
                        </select>
                        <span class="text-muted small">Will allow standard peg auto-deductions (30ml, 60ml, 90ml) in recipes.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Ingredient</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ingredient Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" id="edit_unit" class="form-select" required>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="gm">Gram (gm)</option>
                            <option value="ltr">Liter (ltr)</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="pkt">Packet (pkt)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Per Unit (₹)</label>
                        <input type="number" step="0.01" name="cost_per_unit" id="edit_cost" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" step="0.001" name="stock_quantity" id="edit_stock" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Stock Level</label>
                        <input type="number" step="0.001" name="min_stock_level" id="edit_min" class="form-control" required>
                    </div>
                </div>
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_alcohol" id="edit_is_alcohol" value="1" onchange="toggleEditBottleSize()">
                        <label class="form-check-label fw-bold" for="edit_is_alcohol">🍾 Is Alcoholic Liquor / Bar Item</label>
                    </div>
                    <div class="mb-0 d-none" id="edit_bottle_size_group">
                        <label class="form-label">Bottle Size (ml)</label>
                        <select name="bottle_size_ml" id="edit_bottle_size_ml" class="form-select">
                            <option value="750">750 ml (Standard)</option>
                            <option value="1000">1000 ml (1 Liter)</option>
                            <option value="375">375 ml (Half)</option>
                            <option value="180">180 ml (Quarter)</option>
                            <option value="500">500 ml</option>
                        </select>
                        <span class="text-muted small">Will allow standard peg auto-deductions (30ml, 60ml, 90ml) in recipes.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Ingredient</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleAddBottleSize() {
        if ($('#add_is_alcohol').is(':checked')) {
            $('#add_bottle_size_group').removeClass('d-none');
        } else {
            $('#add_bottle_size_group').addClass('d-none');
        }
    }

    function toggleEditBottleSize() {
        if ($('#edit_is_alcohol').is(':checked')) {
            $('#edit_bottle_size_group').removeClass('d-none');
        } else {
            $('#edit_bottle_size_group').addClass('d-none');
        }
    }

    function editIng(ing) {
        $('#editForm').attr('action', `/cp/inventory/ingredients/${ing.id}`);
        $('#edit_name').val(ing.name);
        $('#edit_unit').val(ing.unit);
        $('#edit_cost').val(ing.cost_per_unit);
        $('#edit_stock').val(ing.stock_quantity);
        $('#edit_min').val(ing.min_stock_level);
        
        const isAlcohol = ing.is_alcohol == 1 || ing.is_alcohol === true;
        $('#edit_is_alcohol').prop('checked', isAlcohol);
        if (isAlcohol) {
            $('#edit_bottle_size_group').removeClass('d-none');
            $('#edit_bottle_size_ml').val(parseFloat(ing.bottle_size_ml).toString());
        } else {
            $('#edit_bottle_size_group').addClass('d-none');
            $('#edit_bottle_size_ml').val('750');
        }
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
</script>
@endsection

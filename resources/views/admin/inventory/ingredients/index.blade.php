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
                            <div class="fw-bold text-dark">{{ $ing->name }}</div>
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
    function editIng(ing) {
        $('#editForm').attr('action', `/cp/inventory/ingredients/${ing.id}`);
        $('#edit_name').val(ing.name);
        $('#edit_unit').val(ing.unit);
        $('#edit_cost').val(ing.cost_per_unit);
        $('#edit_stock').val(ing.stock_quantity);
        $('#edit_min').val(ing.min_stock_level);
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
</script>
@endsection

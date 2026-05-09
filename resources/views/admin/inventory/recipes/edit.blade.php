@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <a href="{{ route('recipes.index') }}" class="btn btn-link text-decoration-none p-0 mb-2">
        <i class="fas fa-arrow-left me-1"></i> Back to Recipes
    </a>
    <h2 class="mb-0">Configure Recipe: {{ $item->name }}</h2>
    <p class="text-muted">Define the ingredients used for this item.</p>
</div>

<form action="{{ route('recipes.update', $item->id) }}" method="POST">
    @csrf @method('PUT')
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Ingredients List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="ingredientsTable">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 30%;">Ingredient</th>
                            <th style="width: 25%;">Variant (Optional)</th>
                            <th style="width: 20%;">Quantity Used</th>
                            <th style="width: 15%;">Unit</th>
                            <th style="width: 10%;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->ingredients as $index => $recipe)
                        <tr>
                            <td>
                                <select name="ingredients[{{ $index }}][ingredient_id]" class="form-select ing-select" required onchange="updateUnit(this)">
                                    <option value="">Select Ingredient</option>
                                    @foreach($ingredients as $ing)
                                        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" {{ $recipe->ingredient_id == $ing->id ? 'selected' : '' }}>
                                            {{ $ing->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="ingredients[{{ $index }}][variant_id]" class="form-select">
                                    <option value="">Base Item (All Variants)</option>
                                    @foreach($item->variants as $v)
                                        <option value="{{ $v->id }}" {{ $recipe->variant_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.001" name="ingredients[{{ $index }}][quantity]" class="form-control" value="{{ $recipe->quantity }}" required>
                            </td>
                            <td class="unit-text text-muted small fw-bold text-uppercase">
                                {{ strtoupper($recipe->ingredient->unit) }}
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="addRow()">
                <i class="fas fa-plus me-1"></i> Add Ingredient to Recipe
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary px-5 py-2">
            <i class="fas fa-save me-2"></i> Save Recipe Configuration
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script>
    let rowIndex = {{ $item->ingredients->count() }};

    function addRow() {
        const tableBody = document.querySelector('#ingredientsTable tbody');
        const row = `
            <tr>
                <td>
                    <select name="ingredients[${rowIndex}][ingredient_id]" class="form-select ing-select" required onchange="updateUnit(this)">
                        <option value="">Select Ingredient</option>
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="ingredients[${rowIndex}][variant_id]" class="form-select">
                        <option value="">Base Item (All Variants)</option>
                        @foreach($item->variants as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" step="0.001" name="ingredients[${rowIndex}][quantity]" class="form-control" required placeholder="0.000">
                </td>
                <td class="unit-text text-muted small fw-bold text-uppercase">-</td>
                <td class="text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
        rowIndex++;
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
    }

    function updateUnit(select) {
        const unit = select.options[select.selectedIndex].dataset.unit;
        select.closest('tr').querySelector('.unit-text').innerText = unit ? unit.toUpperCase() : '-';
    }
</script>
@endsection

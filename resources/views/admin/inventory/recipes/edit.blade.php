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
                                        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-is-alcohol="{{ $ing->is_alcohol ? '1' : '0' }}" data-bottle-size="{{ $ing->bottle_size_ml ?? '750' }}" {{ $recipe->ingredient_id == $ing->id ? 'selected' : '' }}>
                                            {{ $ing->name }} {{ $ing->is_alcohol ? '(🍾)' : '' }}
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
                                <div class="input-group">
                                    <input type="number" step="0.0001" name="ingredients[{{ $index }}][quantity]" class="form-control qty-input" value="{{ $recipe->quantity }}" required>
                                    <select class="form-select peg-helper {{ $recipe->ingredient->is_alcohol ? '' : 'd-none' }}" style="max-width: 140px;" onchange="applyPegHelper(this)">
                                        <option value="">Peg Helper</option>
                                        <option value="30">30 ml Peg</option>
                                        <option value="60">60 ml Peg</option>
                                        <option value="90">90 ml Peg</option>
                                        <option value="750">Full Bottle</option>
                                    </select>
                                </div>
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
                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-is-alcohol="{{ $ing->is_alcohol ? '1' : '0' }}" data-bottle-size="{{ $ing->bottle_size_ml ?? '750' }}">{{ $ing->name }} {{ $ing->is_alcohol ? '(🍾)' : '' }}</option>
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
                    <div class="input-group">
                        <input type="number" step="0.0001" name="ingredients[${rowIndex}][quantity]" class="form-control qty-input" required placeholder="0.0000">
                        <select class="form-select peg-helper d-none" style="max-width: 140px;" onchange="applyPegHelper(this)">
                            <option value="">Peg Helper</option>
                            <option value="30">30 ml Peg</option>
                            <option value="60">60 ml Peg</option>
                            <option value="90">90 ml Peg</option>
                            <option value="750">Full Bottle</option>
                        </select>
                    </div>
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
        const row = select.closest('tr');
        const selectedOption = select.options[select.selectedIndex];
        
        if (!selectedOption || select.value === '') {
            row.querySelector('.unit-text').innerText = '-';
            row.querySelector('.peg-helper').classList.add('d-none');
            return;
        }

        const unit = selectedOption.dataset.unit;
        row.querySelector('.unit-text').innerText = unit ? unit.toUpperCase() : '-';

        const isAlcohol = selectedOption.dataset.isAlcohol === '1';
        const pegHelper = row.querySelector('.peg-helper');
        if (isAlcohol) {
            pegHelper.classList.remove('d-none');
        } else {
            pegHelper.classList.add('d-none');
            pegHelper.value = '';
        }
    }

    function applyPegHelper(helperSelect) {
        const row = helperSelect.closest('tr');
        const ingSelect = row.querySelector('.ing-select');
        const selectedOption = ingSelect.options[ingSelect.selectedIndex];
        
        if (!selectedOption) return;

        const bottleSize = parseFloat(selectedOption.dataset.bottleSize) || 750;
        const pegVol = parseFloat(helperSelect.value);
        
        if (!isNaN(pegVol)) {
            const qty = pegVol / bottleSize;
            // set to 3 decimal places
            row.querySelector('.qty-input').value = qty.toFixed(3);
        }
    }
</script>
@endsection

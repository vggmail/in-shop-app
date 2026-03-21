<?php
$dirV = __DIR__ . '/resources/views/admin/items';

$content = '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-hamburger text-danger me-2"></i> Menu Master</h2>
        <p class="text-muted small">Manage your dishes, variants, and extra toppings.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-dark px-4 rounded-pill shadow-sm"><i class="fas fa-filter small me-2"></i> Filters</button>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="fas fa-plus small me-2"></i> New Item</button>
    </div>
</div>

<div class="row">
    @foreach($items as $i)
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 item-hover">
            <div class="position-relative">
                <div class="bg-light p-5 text-center text-muted">
                    <i class="fas fa-image fa-2x"></i>
                </div>
                <span class="badge bg-white text-dark position-absolute border shadow-sm rounded-pill px-3 py-2" style="top: 15px; right: 15px; font-size: 11px;">
                    <i class="fas fa-circle {{ $i->is_available ? \'text-success\' : \'text-danger\' }} small me-1"></i>
                    {{ $i->is_available ? \'In Stock\' : \'Out of Stock\' }}
                </span>
                <span class="badge bg-danger text-white position-absolute border shadow-sm rounded-pill px-3 py-2" style="top: 15px; left: 15px; font-size: 11px;">
                    {{ $i->category->name }}
                </span>
            </div>
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                    {{ $i->name }}
                    <span class="text-primary small fw-bold">${{ number_format($i->price, 2) }}</span>
                </h5>
                <p class="text-muted small mb-3">Chef\'s Special Item Selection</p>
                
                <div class="d-flex flex-wrap gap-1 mb-4">
                    <span class="badge bg-light text-dark border small fw-normal">{{ $i->variants->count() }} Sizes</span>
                    <span class="badge bg-light text-dark border small fw-normal">{{ $i->extras->count() }} Add-ons</span>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark w-100 rounded-pill py-2"><i class="fas fa-edit small"></i> Edit</button>
                    <form action="{{ route(\'items.destroy\', $i->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-2" onclick="return confirm(\'Remove item from menu?\')"><i class="fas fa-trash small"></i> Del</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="modal fade" id="addItemModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form action="{{ route(\'items.store\') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Register New Dish</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Menu Category</label>
        <select name="category_id" class="form-select form-select-lg mb-3 shadow-none bg-light border-0" required>
            <option value="">Select Category</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Item Display Name</label>
        <input type="text" name="name" class="form-control form-control-lg mb-3 shadow-none bg-light border-0" placeholder="e.g. Classic Beef Burger" required>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Standard Price</label>
        <div class="input-group mb-3">
            <span class="input-group-text bg-white border-0 fw-bold">$</span>
            <input type="number" step="0.01" name="price" class="form-control form-control-lg bg-light border-0 shadow-none" placeholder="0.00" required>
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Availability Status</label>
        <div class="d-flex gap-2 mb-3">
            <input type="radio" class="btn-check" name="is_available" id="avail-y" value="1" checked>
            <label class="btn btn-outline-success border-2 flex-grow-1" for="avail-y"><i class="fas fa-check-circle me-1"></i> In Stock</label>
            <input type="radio" class="btn-check" name="is_available" id="avail-n" value="0">
            <label class="btn btn-outline-danger border-2 flex-grow-1" for="avail-n"><i class="fas fa-times-circle me-1"></i> Out of Stock</label>
        </div>
        
        <div class="p-3 bg-light rounded-3 text-center mb-0">
            <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> You can add Variants (Half/Full) and Extra Toppings after saving the basic item details.</p>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4">
        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-3 fw-bold shadow-sm">CREATE MENU ITEM</button>
    </div>
</form></div></div></div>

<style>
.item-hover { transition: all 0.3s; pointer-events: auto; }
.item-hover:hover { transform: translateY(-10px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; cursor: pointer; }
</style>
@endsection';

file_put_contents("$dirV/index.blade.php", $content);
echo "Menu master revamped.\n";

@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-hamburger text-danger me-2"></i> Menu Master</h2>
        <p class="text-muted small">Manage your dishes, stock quantities, and availability.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
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
                <span class="badge {{ $i->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }} text-white position-absolute border shadow-sm rounded-pill px-3 py-2" style="top: 15px; right: 15px; font-size: 11px;">
                    <i class="fas fa-circle small me-1"></i>
                    {{ $i->stock_quantity > 0 ? 'IN STOCK' : 'OUT OF STOCK' }}
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
                <p class="small mb-3 {{ $i->stock_quantity <= $i->low_stock_limit ? 'text-danger fw-bold' : 'text-muted' }}">
                    Current Stock: {{ $i->stock_quantity }}
                </p>
                
                <div class="d-flex flex-wrap gap-1 mb-4">
                    <span class="badge bg-light text-dark border small fw-normal">{{ $i->variants->count() }} Sizes</span>
                    <span class="badge bg-light text-dark border small fw-normal">{{ $i->extras->count() }} Add-ons</span>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark w-100 rounded-pill py-2" 
                        onclick="editItem({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->category_id }}, {{ $i->price }}, {{ $i->is_available }}, {{ $i->stock_quantity }}, {{ $i->low_stock_limit }})">
                        <i class="fas fa-edit small"></i> Edit
                    </button>
                    <form action="{{ route('items.destroy', $i->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-2" onclick="return confirm('Remove item from menu?')"><i class="fas fa-trash small"></i> Del</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Modal -->
<div class="modal fade" id="addItemModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form action="{{ route('items.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Register New Dish</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Menu Category</label>
                <select name="category_id" class="form-select bg-light border-0 shadow-none" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Standard Price</label>
                <input type="number" step="0.01" name="price" class="form-control bg-light border-0 shadow-none" required>
            </div>
        </div>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Item Display Name</label>
        <input type="text" name="name" class="form-control mb-3 bg-light border-0 shadow-none" placeholder="e.g. Classic Beef Burger" required>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Initial Stock</label>
                <input type="number" name="stock_quantity" class="form-control bg-light border-0 shadow-none" value="100" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Low Stock Alert at</label>
                <input type="number" name="low_stock_limit" class="form-control bg-light border-0 shadow-none" value="10" required>
            </div>
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Force Visibility</label>
        <div class="d-flex gap-2">
            <input type="radio" class="btn-check" name="is_available" id="avail-y" value="1" checked>
            <label class="btn btn-outline-success flex-grow-1" for="avail-y">Enabled</label>
            <input type="radio" class="btn-check" name="is_available" id="avail-n" value="0">
            <label class="btn btn-outline-danger flex-grow-1" for="avail-n">Disabled</label>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-3 fw-bold">CREATE MENU ITEM</button></div>
</form></div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editItemModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form id="editForm" method="POST">
    @csrf @method("PUT")
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Update Dish Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Menu Category</label>
        <select name="category_id" id="edit_category_id" class="form-select mb-3 bg-light border-0" required>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Item Display Name</label>
        <input type="text" name="name" id="edit_name" class="form-control mb-3 bg-light border-0" required>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Price</label>
                <input type="number" step="0.01" name="price" id="edit_price" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Stock</label>
                <input type="number" name="stock_quantity" id="edit_stock" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Low Limit</label>
                <input type="number" name="low_stock_limit" id="edit_limit" class="form-control bg-light border-0" required>
            </div>
        </div>

        <label class="small fw-bold text-muted text-uppercase mb-1">Availability Status</label>
        <div class="d-flex gap-2">
            <input type="radio" class="btn-check" name="is_available" id="edit_avail-y" value="1">
            <label class="btn btn-outline-success flex-grow-1" for="edit_avail-y">In Stock</label>
            <input type="radio" class="btn-check" name="is_available" id="edit_avail-n" value="0">
            <label class="btn btn-outline-danger flex-grow-1" for="edit_avail-n">Out of Stock</label>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-3 fw-bold">SAVE CHANGES</button></div>
</form></div></div></div>

@endsection

@section("scripts")
<script>
function editItem(id, name, catId, price, avail, stock, limit) {
    $("#editForm").attr("action", "/cp/items/" + id);
    $("#edit_name").val(name);
    $("#edit_category_id").val(catId);
    $("#edit_price").val(price);
    $("#edit_stock").val(stock);
    $("#edit_limit").val(limit);
    if(avail == 1) $("#edit_avail-y").prop("checked", true);
    else $("#edit_avail-n").prop("checked", true);
    new bootstrap.Modal(document.getElementById("editItemModal")).show();
}
</script>
@endsection

<style>
.item-hover { transition: all 0.3s; }
.item-hover:hover { transform: translateY(-10px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
</style>

@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-hamburger text-danger me-2"></i> Menu Master</h2>
        <p class="text-muted small">Manage sizes, variations, and extra toppings.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-success px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#bulkUploadModal"><i class="fas fa-file-excel small me-2"></i> Bulk Upload</button>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="fas fa-plus small me-2"></i> New Item</button>
    </div>
</div>

<div class="row">
    @foreach($items as $i)
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 item-hover">
            <div class="position-relative bg-light text-center py-4 text-muted">
                <i class="fas fa-utensils fa-3x"></i>
                <span class="badge {{ $i->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }} position-absolute" style="top:10px; right:10px;">{{ $i->stock_quantity }} in stock</span>
            </div>
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                    {{ $i->name }}
                    <div class="text-end">
                        <span class="text-primary small fw-bold">₹{{ number_format($i->price, 2) }}</span>
                        @if($i->mrp && $i->mrp > $i->price)
                            <div class="text-muted text-decoration-line-through fw-normal" style="font-size: 10px;">₹{{ number_format($i->mrp, 2) }}</div>
                        @endif
                    </div>
                </h5>
                <p class="text-muted small mb-2">{{ $i->category->name }}</p>
                
                <div class="mb-3">
                    <small class="text-uppercase fw-bold text-muted" style="font-size:10px;">Variants:</small>
                    @forelse($i->variants as $v)
                        <span class="badge bg-soft-primary text-primary border-0 me-1">{{ $v->name }} (+₹{{ $v->price }})</span>
                    @empty <span class="text-muted small">No variants</span> @endforelse
                </div>

                <div class="mb-4">
                    <small class="text-uppercase fw-bold text-muted" style="font-size:10px;">Extras:</small>
                    @forelse($i->extras as $e)
                        <span class="badge bg-soft-success text-success border-0 me-1">{{ $e->name }} (+₹{{ $e->price }})</span>
                    @empty <span class="text-muted small">No extras</span> @endforelse
                </div>

                <div class="d-flex gap-2 mt-auto">
                    <button class="btn btn-sm btn-outline-dark w-100 rounded-pill py-2" 
                        onclick="editItem({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->category_id }}, {{ $i->price }}, {{ $i->mrp ?? 'null' }}, {{ $i->is_available }}, {{ $i->stock_quantity }}, {{ $i->low_stock_limit }}, {{ json_encode($i->variants) }}, {{ json_encode($i->extras) }})">
                        <i class="fas fa-edit small me-1"></i> Edit
                    </button>
                    <form action="{{ route('items.destroy', $i->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-2" onclick="return confirm('Delete item?')"><i class="fas fa-trash small me-1"></i> Del</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- BULK UPLOAD MODAL -->
<div class="modal fade" id="bulkUploadModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg">
    <form action="{{ route('items.bulkUpload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold">Bulk Upload Items</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Select CSV File</label>
                <input type="file" name="file" class="form-control bg-light border-0" accept=".csv" required>
            </div>
            <div class="text-end mb-2">
                <a href="{{ route('items.sampleCsv') }}" class="small text-primary fw-bold text-decoration-none"><i class="fas fa-download me-1"></i> Download Sample CSV</a>
            </div>
            <p class="small text-muted mb-0"><b>CSV Format:</b> category_name, name, price, stock_quantity, is_available</p>
        </div>
        <div class="modal-footer border-0 pb-4 pt-0 px-4">
            <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-3 fw-bold shadow-sm"><i class="fas fa-upload me-2"></i> UPLOAD ITEMS</button>
        </div>
    </form>
</div></div></div>

<!-- ITEM MODAL (ADD/EDIT) -->
<div class="modal fade" id="itemModal"><div class="modal-dialog modal-lg"><div class="modal-content border-0 shadow-lg"><form id="itemForm" method="POST">
    @csrf <span id="method_field"></span>
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold" id="itemModalTitle">Manage Menu Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Item Name</label>
                <input type="text" name="name" id="f_name" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-6">
                <label class="small fw-bold text-muted text-uppercase mb-1">Category</label>
                <select name="category_id" id="f_category_id" class="form-select bg-light border-0" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Base Price (₹)</label>
                <input type="number" step="0.01" name="price" id="f_price" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">MRP/Full Price (₹)</label>
                <input type="number" step="0.01" name="mrp" id="f_mrp" class="form-control bg-light border-0">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Stock Qty</label>
                <input type="number" name="stock_quantity" id="f_stock" class="form-control bg-light border-0" value="100" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted text-uppercase mb-1">Low Alert Limit</label>
                <input type="number" name="low_stock_limit" id="f_limit" class="form-control bg-light border-0" value="10" required>
            </div>
        </div>

        <div class="row g-4">
            <!-- Variants -->
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="small fw-bold text-muted text-uppercase">Sizes / Variants</label>
                    <button type="button" class="btn btn-sm btn-primary py-0 px-2" onclick="addRow('v-container', 'variants')">+</button>
                </div>
                <div id="v-container"></div>
            </div>
            <!-- Extras -->
            <div class="col-md-6">
                 <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="small fw-bold text-muted text-uppercase">Extra Toppings</label>
                    <button type="button" class="btn btn-sm btn-success py-0 px-2" onclick="addRow('e-container', 'extras')">+</button>
                </div>
                <div id="e-container"></div>
            </div>
        </div>
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-3 fw-800 shadow-sm">SAVE PRODUCT SETTINGS</button></div>
</form></div></div></div>

<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true"></div>

@endsection

@section("scripts")
<script>
let vIdx = 0; eIdx = 0;

function addRow(containerId, type, name = '', price = '') {
    let idx = type === 'variants' ? vIdx++ : eIdx++;
    let html = `
        <div class="input-group mb-2 shadow-sm rounded overflow-hidden">
            <input type="text" name="${type}[${idx}][name]" value="${name}" class="form-control border-0 bg-white" placeholder="Name" style="font-size:12px;">
            <input type="number" step="0.01" name="${type}[${idx}][price]" value="${price}" class="form-control border-0 bg-white" placeholder="₹ Price" style="font-size:12px;">
            <button class="btn btn-outline-danger border-0 bg-white" type="button" onclick="$(this).parent().remove()"><i class="fas fa-times small"></i></button>
        </div>`;
    $(`#${containerId}`).append(html);
}

function openAddItem() {
    $("#itemForm")[0].reset();
    $("#itemForm").attr("action", "{{ route('items.store') }}");
    $("#method_field").html('');
    $("#itemModalTitle").text("Create New Menu Item");
    $("#v-container, #e-container").empty();
    new bootstrap.Modal(document.getElementById("itemModal")).show();
}

function editItem(id, name, catId, price, mrp, avail, stock, limit, variants, extras) {
    $("#itemForm").attr("action", "/cp/items/" + id);
    $("#method_field").html('@method("PUT")');
    $("#itemModalTitle").text("Edit Item Details");
    $("#f_name").val(name);
    $("#f_category_id").val(catId);
    $("#f_price").val(price);
    $("#f_mrp").val(mrp);
    $("#f_stock").val(stock);
    $("#f_limit").val(limit);
    
    $("#v-container, #e-container").empty();
    variants.forEach(v => addRow('v-container', 'variants', v.name, v.price));
    extras.forEach(e => addRow('e-container', 'extras', e.name, e.price));
    
    new bootstrap.Modal(document.getElementById("itemModal")).show();
}

// Intercept New Item Btn
$('[data-bs-target="#addItemModal"]').attr('data-bs-target', '').attr('onclick', 'openAddItem()');
</script>
@endsection

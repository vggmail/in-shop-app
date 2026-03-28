@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-th-large text-primary me-2"></i> Category Management</h2>
        <p class="text-muted small">Organize your menu with hierarchical categories and subcategories.</p>
    </div>
    <button class="btn btn-primary px-4 rounded-pill shadow-sm" onclick="openAddCategory()">
        <i class="fas fa-plus small me-2"></i> New Category
    </button>
</div>

<div class="row">
    @forelse($categories as $cat)
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 item-hover">
            <div class="position-relative bg-light text-center py-4 text-muted" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                @if($cat->image)
                    <img src="{{ asset('storage/' . $cat->image) }}" class="img-fluid" style="height: 100px; width: 100%; object-fit: contain;">
                @else
                    <i class="fas fa-folder fa-3x opacity-25"></i>
                @endif
                <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-danger' }} position-absolute" style="top:15px; right:15px; font-size: 10px;">
                    {{ $cat->is_active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
            </div>
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1 text-truncate" title="{{ $cat->name }}">{{ $cat->name }}</h5>
                <p class="text-muted small mb-3">
                    @if($cat->parent)
                        <span class="badge bg-light text-dark fw-normal border">
                            <i class="fas fa-level-up-alt fa-rotate-90 me-1 text-primary"></i> {{ $cat->parent->name }}
                        </span>
                    @else
                        <span class="badge bg-soft-primary text-primary fw-normal border-0">Root Category</span>
                    @endif
                </p>
                
                <div class="d-flex justify-content-between mb-4 bg-light p-2 rounded-3">
                    <div class="text-center w-50">
                        <div class="fw-bold text-dark">{{ $cat->children_count }}</div>
                        <div class="small text-muted text-uppercase" style="font-size: 8px; letter-spacing: 0.5px;">Sub-cats</div>
                    </div>
                    <div class="text-center w-50 border-start">
                        <div class="fw-bold text-dark">{{ $cat->items_count }}</div>
                        <div class="small text-muted text-uppercase" style="font-size: 8px; letter-spacing: 0.5px;">Items</div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark w-100 rounded-pill py-2 fw-bold" 
                        onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->parent_id ?? 'null' }}, {{ $cat->is_active }})">
                        <i class="fas fa-edit small me-1"></i> Edit
                    </button>
                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-2 fw-bold" 
                            onclick="confirmAction('Delete category?', 'Are you sure? This will remove subcategories as well.', () => this.closest('form').submit())">
                            <i class="fas fa-trash small me-1"></i> Del
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="mb-3"><i class="fas fa-folder-open fa-4x text-muted opacity-25"></i></div>
        <h5 class="text-muted">No categories found. Start by creating one!</h5>
        <button class="btn btn-primary px-4 rounded-pill mt-3 shadow-sm" onclick="openAddCategory()">
            <i class="fas fa-plus small me-2"></i> Create first category
        </button>
    </div>
    @endforelse
</div>

<!-- CATEGORY MODAL -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="categoryForm" method="POST" enctype="multipart/form-data">
                @csrf <span id="method_field"></span>
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="categoryModalTitle">Manage Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase mb-1">Category Name</label>
                            <input type="text" name="name" id="f_name" class="form-control form-control-lg bg-light border-0 fs-6" placeholder="e.g. Beverages, Fast Food" required>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase mb-1">Parent Category</label>
                            <select name="parent_id" id="f_parent_id" class="form-select form-select-lg bg-light border-0 fs-6">
                                <option value="">None (Root Category)</option>
                                @foreach($allCategories as $ac)
                                    <option value="{{ $ac->id }}">{{ $ac->full_name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Choose a parent if this category should be nested.</div>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase mb-1">Upload Icon/Image</label>
                            <input type="file" name="image_file" class="form-control bg-light border-0" accept="image/*">
                        </div>
                        <div class="col-12 mt-4">
                            <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between bg-light p-3 rounded-3">
                                <label class="form-check-label fw-bold text-muted text-uppercase mb-0 ms-0" for="f_is_active">Show on Menu</label>
                                <input class="form-check-input ms-0 mt-0" style="width: 3em; height: 1.5em; cursor: pointer;" type="checkbox" name="is_active" id="f_is_active" value="1" checked>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 pt-0 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg">
                        <i class="fas fa-save me-2"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section("scripts")
<script>
function openAddCategory() {
    $("#categoryForm")[0].reset();
    $("#categoryForm").attr("action", "{{ route('categories.store') }}");
    $("#method_field").html('');
    $("#categoryModalTitle").text("Add New Category");
    // Show all options in parent selection
    $("#f_parent_id option").show();
    new bootstrap.Modal(document.getElementById("categoryModal")).show();
}

function editCategory(id, name, parentId, isActive) {
    $("#categoryForm").attr("action", "/cp/categories/" + id);
    $("#method_field").html('@method("PUT")');
    $("#categoryModalTitle").text("Edit Category Details");
    $("#f_name").val(name);
    $("#f_parent_id").val(parentId);
    $("#f_is_active").prop('checked', isActive == 1);
    
    // Hide the category itself from parent selection to avoid recursion
    $("#f_parent_id option").show();
    if(id) $("#f_parent_id option[value='" + id + "']").hide();

    new bootstrap.Modal(document.getElementById("categoryModal")).show();
}
</script>

<style>
.bg-soft-primary { background: rgba(13, 110, 253, 0.1); }
.item-hover { transition: all 0.3s ease; }
.item-hover:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
.form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }
</style>
@endsection

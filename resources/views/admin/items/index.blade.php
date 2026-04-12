@extends("layouts.admin")
@section("content")
    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between mb-4 mt-2 gap-3">
        <!-- Left: Title -->
        <div class="flex-shrink-0">
            <h2 class="mb-0 fw-bold fs-4"><i class="fas fa-hamburger text-danger me-2"></i> Menu Master</h2>
        </div>

        <!-- Center: Search & View (Centered) -->
        <div class="d-flex flex-grow-1 align-items-center justify-content-center gap-2 w-100">
            <div class="input-group rounded-pill overflow-hidden shadow-sm border bg-white" style="max-width: 280px;">
                <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="itemSearch" class="form-control border-0 px-1" style="font-size: 14px; box-shadow: none;" placeholder="Search menu..." onkeyup="filterMenu(this.value)">
            </div>
            <div class="btn-group shadow-sm border rounded-pill overflow-hidden bg-white p-1" style="background: #f8fafc; height: 42px;">
                <button class="btn btn-sm btn-light border-0 px-3 active-view" id="btnList" onclick="setViewMode('list')"><i class="fas fa-list"></i></button>
                <button class="btn btn-sm btn-light border-0 px-3" id="btnGrid" onclick="setViewMode('grid')"><i class="fas fa-th-large"></i></button>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="d-flex gap-2 flex-shrink-0">
            <button class="btn btn-outline-success px-3 rounded-pill shadow-sm" style="height: 42px;" data-bs-toggle="modal" data-bs-target="#bulkUploadModal"><i class="fas fa-file-excel small me-2"></i> Bulk</button>
            <button class="btn btn-primary px-3 rounded-pill shadow-sm" style="height: 42px;" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="fas fa-plus small me-2"></i> New Item</button>
        </div>
    </div>

    <div class="row d-none" id="gridView">
        @foreach($items as $i)
            <div class="col-md-3 mb-4 item-search-node">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 item-hover">
                    <div class="position-relative bg-light text-center rounded-top-4 overflow-hidden" style="height: 180px;">
                        @if($i->feature_thumb)
                            <img src="{{ asset('storage/' . $i->feature_thumb) }}" class="w-100 h-100 object-fit-cover"
                                alt="{{ $i->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <i class="fas fa-utensils fa-3x"></i>
                            </div>
                        @endif
                        <span
                            class="badge {{ $i->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }} position-absolute shadow-sm"
                            style="top:10px; right:10px;">{{ $i->stock_quantity }} in stock</span>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1 d-flex justify-content-between align-items-center item-search-name">
                            {{ $i->name }}
                            <div class="text-end">
                                <span class="text-primary small fw-bold">&#8377;{{ number_format($i->price, 2) }}</span>
                                @if($i->mrp && $i->mrp > $i->price)
                                    <div class="text-muted text-decoration-line-through fw-normal" style="font-size: 10px;">
                                        &#8377;{{ number_format($i->mrp, 2) }}</div>
                                @endif
                            </div>
                        </h5>
                        <p class="text-muted small mb-2" title="{{ $i->category->full_name }}">{{ $i->category->full_name }}</p>

                        <div class="mb-3">
                            <small class="text-uppercase fw-bold text-muted" style="font-size:10px;">Variants:</small>
                            @forelse($i->variants as $v)
                                <span class="badge bg-soft-primary text-primary border-0 me-1">{{ $v->name }}
                                    (+&#8377;{{ $v->price }})</span>
                            @empty <span class="text-muted small">No variants</span> @endforelse
                        </div>

                        <div class="mb-4">
                            <small class="text-uppercase fw-bold text-muted" style="font-size:10px;">Extras:</small>
                            @forelse($i->extras as $e)
                                <span class="badge bg-soft-success text-success border-0 me-1">{{ $e->name }}
                                    (+&#8377;{{ $e->price }})</span>
                            @empty <span class="text-muted small">No extras</span> @endforelse
                        </div>

                        <div class="d-flex gap-2 mt-auto">
                            <button class="btn btn-sm btn-outline-dark w-100 rounded-pill"
                                onclick='editItem({{ $i->id }}, {!! json_encode($i->name) !!}, {!! json_encode($i->description) !!}, {!! json_encode($i->default_size) !!}, {{ $i->category_id }}, {{ $i->price }}, {{ $i->mrp ?? "null" }}, {{ $i->is_available }}, {{ $i->stock_quantity }}, {{ $i->low_stock_limit }}, {!! json_encode($i->variants) !!}, {!! json_encode($i->extras) !!}, {!! json_encode($i->image) !!})'>
                                <i class="fas fa-edit small me-1"></i> Edit
                            </button>
                            <form action="{{ route('items.destroy', $i->id) }}" method="POST" class="w-100">
                                @csrf @method("DELETE")
                                <button type="button" class="btn btn-sm btn-outline-danger w-100 rounded-pill"
                                    onclick="confirmAction('Delete Item?', 'Are you sure you want to remove this item?', () => this.closest('form').submit())">
                                    <i class="fas fa-trash small me-1"></i> Del
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4" id="listView">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase" style="font-size: 11px;">
                        <tr>
                            <th class="ps-4 py-3">Item Name</th>
                            <th class="py-3">Category</th>
                            <th class="py-3">Price</th>
                            <th class="py-3">Stock</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i)
                            <tr class="item-search-node">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-3 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px; min-width: 48px; overflow: hidden;">
                                            @if($i->feature_thumb)
                                                <img src="{{ asset('storage/' . $i->feature_thumb) }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="fas fa-utensils text-muted small"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark item-search-name">{{ $i->name }}</div>
                                            <div class="small text-muted">{{ $i->variants->count() }} Variants ·
                                                {{ $i->extras->count() }} Extras
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark fw-normal border">{{ $i->category->name }}</span></td>
                                <td>
                                    <div class="fw-bold">&#8377;{{ number_format($i->price, 2) }}</div>
                                    @if($i->mrp && $i->mrp > $i->price)
                                        <div class="text-muted small text-decoration-line-through">
                                            &#8377;{{ number_format($i->mrp, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="fw-bold {{ $i->stock_quantity <= $i->low_stock_limit ? 'text-danger' : 'text-dark' }}">
                                        {{ $i->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($i->is_available)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill"><i
                                                class="fas fa-check-circle me-1"></i> Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill"><i
                                                class="fas fa-times-circle me-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Edit"
                                            onclick='editItem({{ $i->id }}, {!! json_encode($i->name) !!}, {!! json_encode($i->description) !!}, {!! json_encode($i->default_size) !!}, {{ $i->category_id }}, {{ $i->price }}, {{ $i->mrp ?? "null" }}, {{ $i->is_available }}, {{ $i->stock_quantity }}, {{ $i->low_stock_limit }}, {!! json_encode($i->variants) !!}, {!! json_encode($i->extras) !!}, {!! json_encode($i->image) !!})'>
                                            <i class="fas fa-edit text-primary small me-1"></i> <small
                                                class="fw-bold">Edit</small>
                                        </button>
                                        <form action="{{ route('items.destroy', $i->id) }}" method="POST">
                                            @csrf @method("DELETE")
                                            <button type="button"
                                                class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Delete"
                                                onclick="confirmAction('Delete Item?', 'Are you sure?', () => this.closest('form').submit())">
                                                <i class="fas fa-trash text-danger small me-1"></i> <small
                                                    class="fw-bold">Del</small>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BULK UPLOAD MODAL -->
    <div class="modal fade" id="bulkUploadModal">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
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
                            <a href="{{ route('items.sampleCsv') }}"
                                class="small text-primary fw-bold text-decoration-none"><i class="fas fa-download me-1"></i>
                                Download Sample CSV</a>
                        </div>
                        <div class="alert alert-info py-2 small mb-0 border-0 rounded-3">
                            <b>CSV Format Fields:</b><br>
                            <code>category_name, name, description, default_size, price, mrp, stock_quantity, low_stock_limit, is_available, variants, extras</code><br>
                            <span class="text-muted d-block mt-1" style="font-size: 10px;"><b>Pro Tip:</b> For Variants and Extras, use format <code>Name:Price|Name2:Price2</code> (e.g., <code>Large:50|Double Cheese:100</code>). Let the price be 0 if free.</span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 pt-0 px-4">
                        <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-lg"><i
                                class="fas fa-upload me-2"></i> Upload Items</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ITEM MODAL (ADD/EDIT) -->
    <div class="modal fade" id="itemModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="itemForm" method="POST" enctype="multipart/form-data">
                    @csrf <span id="method_field"></span>
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" id="itemModalTitle">Manage Menu Item</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if($errors->any())
                            <div class="alert alert-danger py-2 small mb-4 border-0 rounded-3 shadow-sm">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Item Name</label>
                                <input type="text" name="name" id="f_name" class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Default Size/Variant</label>
                                <input type="text" name="default_size" id="f_default_size"
                                    class="form-control bg-light border-0" placeholder="e.g. 1 KG">
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select name="category_id" id="f_category_id" class="form-select bg-light border-0"
                                    required>
                                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Item Description</label>
                                <textarea name="description" id="f_description" class="form-control bg-light border-0"
                                    rows="2" placeholder="Brief description of the item..."></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Base Price (&#8377;)</label>
                                <input type="number" step="0.01" name="price" id="f_price"
                                    class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted text-uppercase mb-1">MRP/Full Price (&#8377;)</label>
                                <input type="number" step="0.01" name="mrp" id="f_mrp"
                                    class="form-control bg-light border-0">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Stock Qty</label>
                                <input type="number" name="stock_quantity" id="f_stock"
                                    class="form-control bg-light border-0" value="100" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Low Alert Limit</label>
                                <input type="number" name="low_stock_limit" id="f_limit"
                                    class="form-control bg-light border-0" value="10" required>
                            </div>
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Item Image</label>
                                <div class="d-flex align-items-center bg-light p-2 rounded-3">
                                    <div id="image_preview" class="me-3 rounded shadow-sm overflow-hidden d-none"
                                        style="width: 60px; height: 60px; min-width: 60px;">
                                        <img src="" id="img_preview_tag" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <input type="file" name="image" id="f_image"
                                        class="form-control bg-transparent border-0" accept="image/*" onchange="checkImageSize(this)">
                                </div>
                                <div id="image-size-error" class="text-danger fw-bold mt-1 d-none" style="font-size: 11px;"><i class="fas fa-exclamation-circle me-1"></i> File is too large! Maximum allowed is 2MB.</div>
                                <small class="text-muted" style="font-size: 10px;">Best size: 500x500px square (1:1 ratio).
                                    Max 2MB. PNG/JPG/WebP. Leave empty to keep existing.</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Variants -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="small fw-bold text-muted text-uppercase">Sizes / Variants</label>
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2"
                                        onclick="addRow('v-container', 'variants')">+</button>
                                </div>
                                <div id="v-container"></div>
                            </div>
                            <!-- Extras -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="small fw-bold text-muted text-uppercase">Extra Toppings</label>
                                    <button type="button" class="btn btn-sm btn-success py-0 px-2"
                                        onclick="addRow('e-container', 'extras')">+</button>
                                </div>
                                <div id="e-container"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 pt-0 px-4">
                        <button type="submit" id="btnItemSave" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-lg"><i
                                class="fas fa-save me-2"></i> Save Product Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    <input type="number" step="0.01" name="${type}[${idx}][price]" value="${price}" class="form-control border-0 bg-white" placeholder="&#8377; Price" style="font-size:12px;">
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

        function editItem(id, name, description, defaultSize, catId, price, mrp, avail, stock, limit, variants, extras, image = null) {
            $("#itemForm").attr("action", "/cp/items/" + id);
            $("#method_field").html('@method("PUT")');
            $("#itemModalTitle").text("Edit Item Details");
            $("#f_name").val(name);
            $("#f_description").val(description);
            $("#f_default_size").val(defaultSize);
            $("#f_category_id").val(catId);
            $("#f_price").val(price);
            $("#f_mrp").val(mrp);
            $("#f_stock").val(stock);
            $("#f_limit").val(limit);
            $("#f_image").val('');

            if (image) {
                $("#image_preview").removeClass('d-none');
                $("#img_preview_tag").attr('src', '/storage/' + image);
            } else {
                $("#image_preview").addClass('d-none');
            }

            $("#v-container, #e-container").empty();
            variants.forEach(v => addRow('v-container', 'variants', v.name, v.price));
            extras.forEach(e => addRow('e-container', 'extras', e.name, e.price));

            new bootstrap.Modal(document.getElementById("itemModal")).show();
        }

        // Intercept New Item Btn
        $('[data-bs-target="#addItemModal"]').attr('data-bs-target', '').attr('onclick', 'openAddItem()');
    </script>

    <script>
        function setViewMode(mode) {
            localStorage.setItem('menu_view_mode', mode);
            if (mode === 'list') {
                $('#gridView').addClass('d-none');
                $('#listView').removeClass('d-none');
                $('#btnList').addClass('active-view bg-primary text-white').removeClass('btn-light');
                $('#btnGrid').addClass('btn-light').removeClass('active-view bg-primary text-white');
            } else {
                $('#listView').addClass('d-none');
                $('#gridView').removeClass('row').addClass('row').removeClass('d-none');
                $('#btnGrid').addClass('active-view bg-primary text-white').removeClass('btn-light');
                $('#btnList').addClass('btn-light').removeClass('active-view bg-primary text-white');
            }
        }

        function filterMenu(query) {
            query = query.toLowerCase();
            $('.item-search-node').each(function() {
                let name = $(this).find('.item-search-name').text().toLowerCase();
                if(name.includes(query)) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        }

        function checkImageSize(input) {
            const file = input.files[0];
            const errorDiv = $('#image-size-error');
            const preview = $('#image_preview');
            const previewTag = $('#img_preview_tag');
            const saveBtn = $('#btnItemSave');

            if (file) {
                if (file.size > 2 * 1024 * 1024) { 
                    errorDiv.removeClass('d-none');
                    // input.value = ""; // Don't clear to let user see it's wrong, but block save
                    saveBtn.prop('disabled', true).addClass('opacity-50');
                    preview.addClass('d-none');
                } else {
                    errorDiv.addClass('d-none');
                    saveBtn.prop('disabled', false).removeClass('opacity-50');
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        preview.removeClass('d-none');
                        previewTag.attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                errorDiv.addClass('d-none');
                saveBtn.prop('disabled', false).removeClass('opacity-50');
            }
        }

        $(document).ready(function () {
            let mode = localStorage.getItem('menu_view_mode') || 'list';
            setViewMode(mode);

            @if($errors->any())
                new bootstrap.Modal(document.getElementById('itemModal')).show();
            @endif
        });
    </script>
@endsection
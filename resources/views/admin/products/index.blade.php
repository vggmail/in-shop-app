@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Products</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Product</button>
</div>
<div class="card bg-white p-3">
    <table class="table table-bordered table-striped">
        <thead><tr><th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($products as $p)
            <tr>
                <td>-</td>
                <td>{{ $p->name }}<br><small class="text-muted">{{ $p->category->name }}</small></td>
                <td>{{ $p->sku }}</td>
                <td>${{ $p->price }}</td>
                <td>
                    @if($p->stock_quantity <= $p->low_stock_alert)
                        <span class="badge bg-danger">{{ $p->stock_quantity }}</span>
                    @else
                        <span class="badge bg-success">{{ $p->stock_quantity }}</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="edit({{ $p->id }})">Edit</button>
                    <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Sure?')">Del</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('products.store') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Add Product</h5></div>
    <div class="modal-body p-3">
        <select name="category_id" class="form-control mb-2" required>
            <option value="">Select Category</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
        <input type="text" name="sku" class="form-control mb-2" placeholder="SKU" required>
        <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
        <input type="number" name="stock_quantity" class="form-control mb-2" placeholder="Stock Qty" required>
        <input type="number" name="low_stock_alert" value="5" class="form-control mb-2" placeholder="Low Stock Alert" required>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div></div>
@endsection
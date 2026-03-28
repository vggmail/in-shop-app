<?php
$dirV = __DIR__ . '/resources/views/admin';

if(!is_dir("$dirV/items")) mkdir("$dirV/items", 0777, true);
if(!is_dir("$dirV/expenses")) mkdir("$dirV/expenses", 0777, true);

// Items Index
file_put_contents("$dirV/items/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Menu Items</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Item</button>
</div>
<div class="card bg-white p-3 shadow-sm border-0">
    <table class="table table-hover">
        <thead class="table-light"><tr><th>Category</th><th>Name</th><th>Base Price</th><th>Status</th><th>Extras / Variants</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($items as $i)
            <tr>
                <td><span class="badge bg-secondary">{{ $i->category->name }}</span></td>
                <td class="font-weight-bold">{{ $i->name }}</td>
                <td>${{ $i->price }}</td>
                <td>
                    @if($i->is_available)<span class="badge bg-success">Available</span>@else<span class="badge bg-danger">Out of Stock</span>@endif
                </td>
                <td>
                    <small class="text-muted">Var: {{ $i->variants->count() }} | Ext: {{ $i->extras->count() }}</small>
                </td>
                <td>
                    <form action="{{ route(\'items.destroy\', $i->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete item?\')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route(\'items.store\') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Add Menu Item</h5></div>
    <div class="modal-body p-3">
        <select name="category_id" class="form-control mb-3" required>
            <option value="">Select Category</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input type="text" name="name" class="form-control mb-3" placeholder="Item Name (e.g. Chicken Burger)" required>
        <div class="input-group mb-3">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Base Price" required>
        </div>
        <select name="is_available" class="form-control mb-3">
            <option value="1">Available</option>
            <option value="0">Out of Stock</option>
        </select>
        <small class="text-danger">* Note: Use DB directly to add variants/extras for now.</small>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Item</button></div>
</form></div></div></div>
@endsection');


// Expenses Index
file_put_contents("$dirV/expenses/index.blade.php", '@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-3">
    <h2>Expenses</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Expense</button>
</div>
<div class="card bg-white p-3 shadow-sm border-0">
    <table class="table table-hover">
        <thead class="table-light"><tr><th>Date</th><th>Category</th><th>Amount</th><th>Description</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td>{{ $e->date }}</td>
                <td><span class="badge bg-info text-dark">{{ $e->category }}</span></td>
                <td class="text-danger font-weight-bold">-${{ number_format($e->amount, 2) }}</td>
                <td>{{ $e->description }}</td>
                <td>
                    <form action="{{ route(\'expenses.destroy\', $e->id) }}" method="POST" class="d-inline">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete expense?\')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><form action="{{ route(\'expenses.store\') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Record Expense</h5></div>
    <div class="modal-body p-3">
        <label>Expense Category</label>
        <select name="category" class="form-control mb-3" required>
            <option value="Rent">Rent</option>
            <option value="Salary">Salary</option>
            <option value="Raw Material">Raw Material</option>
            <option value="Electricity">Electricity</option>
            <option value="Other">Other Expenses</option>
        </select>
        <label>Amount</label>
        <div class="input-group mb-3">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <label>Date</label>
        <input type="date" name="date" class="form-control mb-3" required value="{{ date("Y-m-d") }}">
        <label>Details</label>
        <textarea name="description" class="form-control mb-3" rows="2" placeholder="Brief note..."></textarea>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Expense</button></div>
</form></div></div></div>
@endsection');

echo "Items and Expenses views created.\n";

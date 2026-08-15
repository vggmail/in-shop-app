@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Recipe Builder</h2>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Menu Item</th>
                        <th>Category</th>
                        <th>Ingredients Configured</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-utensils text-muted"></i>
                                    </div>
                                @endif
                                <div class="fw-bold text-dark">{{ $item->name }}</div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $item->category->name }}</span></td>
                        <td>
                            @php $count = $item->ingredients->count(); @endphp
                            @if($count > 0)
                                <span class="badge bg-success-soft text-success">{{ $count }} Ingredients Linked</span>
                            @else
                                <span class="badge bg-warning-soft text-warning">No Recipe Defined</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('recipes.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-tools me-1"></i> Configure Recipe
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: #ecfdf5; }
    .bg-warning-soft { background-color: #fffbeb; }
</style>
@endsection

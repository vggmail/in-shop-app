@extends("layouts.admin")

@section("content")
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-800 text-dark mb-1">Tenant Management</h2>
            <p class="text-muted mb-0">Manage your shops and subdomains</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary shadow-sm px-4 py-2 rounded-pill">
                <i class="fas fa-plus me-2"></i> Create New Tenant
            </a>
        </div>
    </div>

    <div class="mb-4">
        <div class="btn-group p-1 bg-light rounded-pill">
            <a href="{{ route('super-admin.tenants.index', ['tab' => 'active']) }}" class="btn rounded-pill px-4 {{ $tab === 'active' ? 'btn-white shadow-sm fw-bold text-primary' : 'btn-link text-muted text-decoration-none' }}">
                Active Shops
            </a>
            <a href="{{ route('super-admin.tenants.index', ['tab' => 'archived']) }}" class="btn rounded-pill px-4 {{ $tab === 'archived' ? 'btn-white shadow-sm fw-bold text-primary' : 'btn-link text-muted text-decoration-none' }}">
                Archived
                @php $trashCount = \App\Models\Tenant::onlyTrashed()->count(); @endphp
                @if($trashCount > 0) <span class="badge bg-danger ms-1 small">{{ $trashCount }}</span> @endif
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 border-0 text-uppercase small fw-bold">Shop Details</th>
                            <th class="py-3 border-0 text-uppercase small fw-bold">Subdomain</th>
                            <th class="py-3 border-0 text-uppercase small fw-bold text-center">Status</th>
                            <th class="py-3 border-0 text-uppercase small fw-bold">{{ $tab === 'archived' ? 'Deleted At' : 'Created At' }}</th>
                            <th class="px-4 py-3 border-0 text-end text-uppercase small fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $t)
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-3 p-2 me-3">
                                        <i class="fas fa-store text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $t->name }}</h6>
                                        <small class="text-muted d-block">{{ $t->phone ?? 'No phone' }}</small>
                                        @if($t->expires_at)
                                            <small class="{{ $t->expires_at->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fas fa-calendar-times me-1"></i> Expiry: {{ $t->expires_at->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="http://{{ $t->subdomain }}.localhost" target="_blank" class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2 text-decoration-none">
                                    <i class="fas fa-external-link-alt me-1"></i> {{ $t->subdomain }}
                                </a>
                            </td>
                            <td class="text-center">
                                @if($t->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill mb-1">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill mb-1">Inactive</span>
                                @endif
                                
                                @if($t->disable_home_page)
                                    <div class="mt-1"><span class="badge bg-warning-subtle text-dark border border-warning border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 0.65rem;">Storefront Disabled</span></div>
                                @endif
                            </td>
                            <td>
                                @if($tab === 'archived')
                                    <div class="small text-danger fw-bold">{{ $t->deleted_at->format('d M Y') }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $t->deleted_at->diffForHumans() }}</div>
                                @else
                                    <div class="small text-dark">{{ $t->created_at->format('d M Y') }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $t->created_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="px-4 text-end">
                                @if($tab === 'archived')
                                    <form action="{{ route('super-admin.tenants.restore', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success border-0 shadow-none px-3">
                                            <i class="fas fa-undo-alt me-1"></i> Restore
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('super-admin.tenants.toggle', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $t->is_active ? 'text-warning' : 'text-success' }} border-0 shadow-none" title="{{ $t->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $t->is_active ? 'fa-pause-circle' : 'fa-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('super-admin.tenants.edit', $t->id) }}" class="btn btn-sm text-primary border-0 shadow-none" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('super-admin.tenants.destroy', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tenant?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm text-danger border-0 shadow-none ms-1" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center">
                                <i class="fas fa-store fa-3x text-light mb-3 d-block"></i>
                                <p class="text-muted mb-0">No tenants found</p>
                                <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-link text-primary mt-2">Create your first tenant</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

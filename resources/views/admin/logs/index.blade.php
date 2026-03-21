@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-history text-primary me-2"></i> Activity Tracker</h2>
        <p class="text-muted small">Monitoring system actions and administrative changes.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="px-4 py-3 border-0 small fw-800 text-muted text-uppercase">Timestamp</th>
                    <th class="py-3 border-0 small fw-800 text-muted text-uppercase">Admin</th>
                    <th class="py-3 border-0 small fw-800 text-muted text-uppercase">Action</th>
                    <th class="py-3 border-0 small fw-800 text-muted text-uppercase">Resources</th>
                    <th class="py-3 border-0 small fw-800 text-muted text-uppercase text-end px-4">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td class="px-4 py-3 small border-0">
                        <div class="fw-bold text-dark">{{ $log->created_at->format('d M, Y') }}</div>
                        <div class="text-muted smaller">{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="py-3 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 small" style="width: 28px; height: 28px;">
                                {{ substr($log->user->name ?? 'S', 0, 1) }}
                            </div>
                            <span class="fw-bold small text-dark">{{ $log->user->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td class="py-3 border-0">
                        <span class="badge {{ str_contains($log->action, 'Deleted') ? 'bg-soft-danger text-danger' : (str_contains($log->action, 'Created') ? 'bg-soft-success text-success' : 'bg-soft-primary text-primary') }} border-0 px-3 py-2 rounded-pill small">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="py-3 border-0">
                        <div class="small text-dark fw-600">{{ $log->model_type }}</div>
                        <div class="text-muted smaller">ID: #{{ $log->model_id }}</div>
                    </td>
                    <td class="py-3 border-0 text-end px-4">
                        <code class="bg-light px-2 py-1 rounded small text-secondary">{{ $log->ip_address }}</code>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $logs->links("pagination::bootstrap-5") }}
</div>

<style>
    .smaller { font-size: 0.75rem; }
</style>
@endsection

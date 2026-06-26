@extends('admin.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Security Audit Logs</h4>
        <p class="text-muted mb-0">Track administrative actions and system modifications.</p>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search User or Action</label>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Action Type</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="approved" {{ request('action') == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP / Browser</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td class="small">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $log->admin->name ?? 'System' }}</div>
                        <div class="small text-muted">{{ $log->admin->email ?? '' }}</div>
                    </td>
                    <td>
                        @php
                            $actionClass = match($log->action) {
                                'created' => 'text-success',
                                'updated' => 'text-primary',
                                'deleted' => 'text-danger',
                                default => 'text-secondary'
                            };
                        @endphp
                        <span class="fw-bold {{ $actionClass }}">{{ strtoupper($log->action) }}</span>
                    </td>
                    <td>
                        @if($log->model_type)
                            @php
                                $modelName = class_basename($log->model_type);
                            @endphp
                            <span class="badge bg-light text-dark border">{{ $modelName }} #{{ $log->model_id }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($log->description, 40) }}</td>
                    <td class="small">
                        <div title="{{ $log->user_agent }}">{{ $log->ip_address }}</div>
                    </td>
                    <td>
                        @if($log->old_values || $log->new_values)
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalLog{{ $log->id }}">
                                View JSON
                            </button>
                            
                            {{-- Modal --}}
                            <div class="modal fade" id="modalLog{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Changes Detail</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <h6>Old Values</h6>
                                                    <pre class="bg-light p-2 rounded" style="font-size:12px;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                                <div class="col-6">
                                                    <h6>New Values</h6>
                                                    <pre class="bg-light p-2 rounded border-primary" style="font-size:12px;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted small">No diff</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No audit logs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
    <div class="p-3 border-top">
        {{ $auditLogs->links() }}
    </div>
    @endif
</div>
@endsection

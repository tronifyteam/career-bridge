@extends('admin.layouts.app')

@section('title', 'Worker Verification')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i>Worker Verification</h4>
        <p class="text-muted mb-0">Manage worker document verification, selfie approval & badge status</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-warning text-dark fs-6">
            {{ $workers->total() }} Workers
        </span>
    </div>
</div>

{{-- Stats Bar --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-warning bg-opacity-15 text-warning">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.5rem;">
                    {{ $workers->filter(fn($w) => $w->verified_badge_status === 'pending')->count() }}
                </div>
                <div class="stat-label">Pending Badge</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-success bg-opacity-15 text-success">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.5rem;">
                    {{ $workers->filter(fn($w) => $w->verified_badge_status === 'verified')->count() }}
                </div>
                <div class="stat-label">Verified</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-info bg-opacity-15 text-info">
                <i class="bi bi-person-check"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.5rem;">
                    {{ $workers->filter(fn($w) => $w->ready_to_work_status === 'ready')->count() }}
                </div>
                <div class="stat-label">Ready to Work</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-danger bg-opacity-15 text-danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.5rem;">
                    {{ $workers->filter(fn($w) => $w->verified_badge_status === 'rejected')->count() }}
                </div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.workers.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, nationality..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Badge Status</label>
                <select name="badge_status" class="form-select">
                    <option value="">All Badges</option>
                    <option value="unverified" {{ request('badge_status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                    <option value="pending" {{ request('badge_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ request('badge_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ request('badge_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Ready to Work</label>
                <select name="ready_status" class="form-select">
                    <option value="">All Status</option>
                    <option value="not_ready" {{ request('ready_status') == 'not_ready' ? 'selected' : '' }}>Not Ready</option>
                    <option value="pending" {{ request('ready_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="ready" {{ request('ready_status') == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="rejected" {{ request('ready_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Has Selfie</label>
                <select name="has_selfie" class="form-select">
                    <option value="">All</option>
                    <option value="yes" {{ request('has_selfie') == 'yes' ? 'selected' : '' }}>Has Selfie</option>
                    <option value="no" {{ request('has_selfie') == 'no' ? 'selected' : '' }}>No Selfie</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.workers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Workers Table --}}
<div class="card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Workers List</span>
        <small class="text-muted">{{ $workers->total() }} total · Page {{ $workers->currentPage() }} of {{ $workers->lastPage() }}</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Worker</th>
                    <th>Nationality</th>
                    <th>Selfie</th>
                    <th>Verified Badge</th>
                    <th>Ready to Work</th>
                    <th>Docs Uploaded</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workers as $worker)
                <tr>
                    <td class="text-muted small">{{ $worker->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $worker->full_name ?? $worker->name }}</div>
                        <small class="text-muted">{{ $worker->email }}</small>
                    </td>
                    <td class="small">{{ $worker->nationality ?? '—' }}</td>
                    <td>
                        @if($worker->selfie_file_url)
                            @if($worker->selfie_verified_at)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-camera me-1"></i>Uploaded
                                </span>
                            @endif
                        @else
                            <span class="text-muted small"><i class="bi bi-x-circle me-1"></i>None</span>
                        @endif
                    </td>
                    <td>
                        @php $badge = $worker->verified_badge_status ?? 'unverified'; @endphp
                        <span class="badge badge-role badge-status-{{ $badge }}">
                            @if($badge === 'verified') <i class="bi bi-patch-check-fill me-1"></i>
                            @elseif($badge === 'pending') <i class="bi bi-hourglass-split me-1"></i>
                            @elseif($badge === 'rejected') <i class="bi bi-x-circle me-1"></i>
                            @else <i class="bi bi-dash-circle me-1"></i>
                            @endif
                            {{ ucfirst($badge) }}
                        </span>
                    </td>
                    <td>
                        @php $rtw = $worker->ready_to_work_status ?? 'not_ready'; @endphp
                        <span class="badge badge-role badge-status-{{ $rtw === 'ready' ? 'approved' : ($rtw === 'pending' ? 'pending' : ($rtw === 'rejected' ? 'rejected' : 'unverified')) }}">
                            {{ ucfirst(str_replace('_', ' ', $rtw)) }}
                        </span>
                    </td>
                    <td>
                        @php $docCount = $worker->workerDocuments->count() ?? 0; @endphp
                        @if($docCount > 0)
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                <i class="bi bi-file-earmark me-1"></i>{{ $docCount }} doc{{ $docCount > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="text-muted small">No docs</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $worker->created_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.workers.show', $worker) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-shield-check me-1"></i>Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                        No workers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workers->hasPages())
    <div class="p-3 border-top">
        {{ $workers->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

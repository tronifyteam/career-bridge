@extends('admin.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Applications</h4>
        <p class="text-muted mb-0">Manage job applications</p>
    </div>
</div>

{{-- Filter --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.applications.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Applications Table --}}
<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Job</th>
                    <th>Employer</th>
                    <th>Cover Letter</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td class="text-muted">{{ $app->id }}</td>
                    <td>
                        <div class="fw-semibold small">{{ $app->user?->full_name ?? 'Unknown' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $app->user?->email }}</div>
                    </td>
                    <td class="small">
                        @if($app->job)
                            <a href="{{ route('admin.jobs.show', $app->job) }}" class="text-decoration-none">{{ $app->job->title }}</a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </td>
                    <td class="small">{{ $app->job?->employer_name ?? '—' }}</td>
                    <td class="small">{{ $app->cover_letter ? Str::limit($app->cover_letter, 50) : '—' }}</td>
                    <td><span class="badge badge-role badge-status-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                    <td class="small text-muted">{{ $app->applied_at?->format('M d, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.applications.updateStatus', $app) }}" class="d-inline">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                                <option value="pending" {{ $app->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reviewed" {{ $app->status == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="accepted" {{ $app->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="rejected" {{ $app->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No applications found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
    <div class="p-3 border-top">
        {{ $applications->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

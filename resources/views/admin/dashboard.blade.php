@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Dashboard</h4>
        <p class="text-muted mb-0">Overview of 2ne5 platform</p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-clock"></i> {{ now()->format('l, M d Y — H:i') }}
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-value">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-value">{{ $stats['active_jobs'] }}</div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                <i class="bi bi-briefcase-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-value">{{ ($stats['pending_worker_docs'] ?? 0) + ($stats['pending_employer_docs'] ?? 0) }}</div>
                <div class="stat-label">Docs Pending Review</div>
            </div>
            <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                <i class="bi bi-file-earmark-check-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-value">{{ $stats['pending_employer_accounts'] ?? 0 }}</div>
                <div class="stat-label">Employer Unverified</div>
            </div>
            <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                <i class="bi bi-building-exclamation"></i>
            </div>
        </div>
    </div>
</div>

{{-- Quick Links Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('admin.workers.index') }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-0" style="width:40px;height:40px;font-size:1.2rem;">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="fw-semibold">Review Workers</div>
                    <div class="small text-muted">{{ $stats['pending_worker_docs'] ?? 0 }} doc(s) pending</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.employers.index') }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3 py-3">
                <div class="stat-icon mb-0" style="background:rgba(16,185,129,0.1);color:#10b981;width:40px;height:40px;font-size:1.2rem;">
                    <i class="bi bi-building-fill-check"></i>
                </div>
                <div>
                    <div class="fw-semibold">Review Employers</div>
                    <div class="small text-muted">{{ ($stats['pending_employer_docs'] ?? 0) + ($stats['pending_employer_accounts'] ?? 0) }} pending</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.jobs.index') }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3 py-3">
                <div class="stat-icon mb-0" style="background:rgba(99,102,241,0.1);color:#6366f1;width:40px;height:40px;font-size:1.2rem;">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <div class="fw-semibold">Moderate Jobs</div>
                    <div class="small text-muted">{{ $stats['total_jobs'] }} total jobs</div>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
</div>


{{-- Role Breakdown --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="stat-label fw-semibold">Workers</span>
                <span class="badge badge-role badge-worker">{{ $stats['total_workers'] }}</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-primary" style="width: {{ $stats['total_users'] > 0 ? ($stats['total_workers'] / $stats['total_users'] * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="stat-label fw-semibold">Employers</span>
                <span class="badge badge-role badge-company">{{ $stats['total_employers'] }}</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: {{ $stats['total_users'] > 0 ? ($stats['total_employers'] / $stats['total_users'] * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent Jobs --}}
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-briefcase me-2"></i>Recent Jobs</span>
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th>Location</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentJobs as $job)
                        <tr>
                            <td>
                                <a href="{{ route('admin.jobs.show', $job) }}" class="text-decoration-none fw-semibold">{{ $job->title }}</a>
                                <div class="text-muted small">{{ $job->employer_name }}</div>
                            </td>
                            <td class="small">{{ $job->location }}</td>
                            <td><span class="badge badge-role badge-status-{{ $job->status }}">{{ ucfirst($job->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No jobs yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Applications --}}
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2"></i>Recent Applications</span>
                <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Applicant</th><th>Job</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentApplications as $app)
                        <tr>
                            <td class="small">{{ $app->user?->full_name ?? 'Unknown' }}</td>
                            <td class="small">{{ $app->job?->title ?? 'Unknown' }}</td>
                            <td><span class="badge badge-role badge-status-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No applications yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

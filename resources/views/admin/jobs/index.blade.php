@extends('admin.layouts.app')

@section('title', 'Jobs — Review Queue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Jobs</h4>
        <p class="text-muted mb-0">Review queue &amp; fake vacancy detection</p>
    </div>
    <div class="d-flex gap-2">
        {{-- Bulk Screen Button --}}
        <form action="{{ route('admin.jobs.bulkScreen') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-shield-exclamation me-1"></i> Screen All Pending
            </button>
        </form>
    </div>
</div>

{{-- M5: Risk Stats Bar --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Jobs</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center" style="border-left: 4px solid #f59e0b;">
            <div class="stat-value text-warning">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending Review</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center" style="border-left: 4px solid #ef4444;">
            <div class="stat-value text-danger">{{ $stats['critical'] }}</div>
            <div class="stat-label">Critical Risk</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center" style="border-left: 4px solid #f97316;">
            <div class="stat-value" style="color: #f97316;">{{ $stats['high'] }}</div>
            <div class="stat-label">High Risk</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Title, employer, location..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="submitted_for_review" {{ request('status') == 'submitted_for_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            {{-- M5: Risk Level Filter --}}
            <div class="col-md-2">
                <label class="form-label small">Risk Level</label>
                <select name="risk_level" class="form-select">
                    <option value="">All Risk Levels</option>
                    <option value="critical" {{ request('risk_level') == 'critical' ? 'selected' : '' }}>🔴 Critical</option>
                    <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>🟠 High</option>
                    <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                    <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>🟢 Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Employer Type</label>
                <select name="employer_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="company" {{ request('employer_type') == 'company' ? 'selected' : '' }}>Company</option>
                    <option value="factory" {{ request('employer_type') == 'factory' ? 'selected' : '' }}>Factory</option>
                    <option value="family_care" {{ request('employer_type') == 'family_care' ? 'selected' : '' }}>Family Care</option>
                    <option value="agency" {{ request('employer_type') == 'agency' ? 'selected' : '' }}>Agency</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Jobs Table --}}
<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Employer</th>
                    <th>Type</th>
                    <th>Salary</th>
                    <th>Apps</th>
                    <th>Status</th>
                    <th>Risk</th>
                    <th>Flags</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                @php
                    $risk = $job->risk_level ?? 'low';
                    $flagCount = count($job->red_flags ?? []) + count($job->missing_fields ?? []);
                    $riskColors = [
                        'critical' => ['bg' => '#fef2f2', 'border' => '#fca5a5', 'badge' => 'danger'],
                        'high'     => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'badge' => 'warning'],
                        'medium'   => ['bg' => '#fffbeb', 'border' => '#fde68a', 'badge' => 'info'],
                        'low'      => ['bg' => '#ffffff', 'border' => '#e2e8f0', 'badge' => 'success'],
                    ];
                    $rc = $riskColors[$risk] ?? $riskColors['low'];
                @endphp
                <tr style="background: {{ $rc['bg'] }}; border-left: 3px solid {{ $rc['border'] }};">
                    <td class="text-muted small">{{ $job->id }}</td>
                    <td>
                        <a href="{{ route('admin.jobs.show', $job) }}" class="text-decoration-none fw-semibold">{{ $job->title }}</a>
                        @if($job->is_urgent)
                            <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">URGENT</span>
                        @endif
                        @if($job->screened_at)
                            <span class="badge bg-light text-muted border ms-1" style="font-size: 0.6rem;" title="Last screened {{ $job->screened_at->diffForHumans() }}">
                                <i class="bi bi-shield-check"></i>
                            </span>
                        @else
                            <span class="badge bg-secondary ms-1" style="font-size: 0.6rem;" title="Not yet screened">
                                <i class="bi bi-shield-x"></i>
                            </span>
                        @endif
                    </td>
                    <td class="small">{{ $job->employer_name }}</td>
                    <td>
                        <span class="badge badge-role badge-{{ $job->employer_type }}">
                            {{ str_replace('_', ' ', ucfirst($job->employer_type)) }}
                        </span>
                    </td>
                    <td class="small">{{ $job->salary }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $job->applications_count }}</span>
                    </td>
                    <td><span class="badge badge-role badge-status-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>
                    <td>
                        @if($risk === 'critical')
                            <span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</span>
                        @elseif($risk === 'high')
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>High</span>
                        @elseif($risk === 'medium')
                            <span class="badge bg-info text-dark">Medium</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success">Low</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($flagCount > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger" title="{{ implode(', ', $job->red_flags ?? []) }}">
                                <i class="bi bi-flag-fill"></i> {{ $flagCount }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $job->posted_at?->format('M d') }}</td>
                    <td>
                        <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted py-4">No jobs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($jobs->hasPages())
    <div class="p-3 border-top">
        {{ $jobs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

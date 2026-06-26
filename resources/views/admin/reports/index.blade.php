@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Reports & Trust System</h4>
        <p class="text-muted mb-0">Review user reports for fake jobs, spam, and abuse.</p>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>Investigating</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Type</label>
                <select name="report_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="job" {{ request('report_type') == 'job' ? 'selected' : '' }}>Job</option>
                    <option value="user" {{ request('report_type') == 'user' ? 'selected' : '' }}>User</option>
                    <option value="chat" {{ request('report_type') == 'chat' ? 'selected' : '' }}>Chat</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Reporter</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td class="text-muted">#{{ $report->id }}</td>
                    <td class="small">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $report->reporter->full_name ?? $report->reporter->name ?? 'Unknown' }}</div>
                        <div class="small text-muted">{{ $report->reporter->email ?? '' }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ ucfirst($report->report_type) }}</span></td>
                    <td>
                        @if($report->report_type === 'job' && $report->job)
                            <a href="{{ route('admin.jobs.show', $report->job_id) }}" class="text-primary">{{ Str::limit($report->job->title, 20) }}</a>
                        @elseif($report->report_type === 'user' && $report->reported)
                            <a href="{{ route('admin.users.show', $report->reported_id) }}" class="text-primary">{{ $report->reported->name }}</a>
                        @else
                            ID: {{ $report->reported_id ?? $report->job_id ?? $report->chat_message_id }}
                        @endif
                    </td>
                    <td>{{ Str::limit($report->reason, 30) }}</td>
                    <td>
                        @php
                            $badgeClass = match($report->status) {
                                'pending' => 'bg-warning text-dark',
                                'investigating' => 'bg-info text-dark',
                                'resolved' => 'bg-success',
                                'dismissed' => 'bg-secondary',
                                default => 'bg-light text-dark'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($report->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No reports found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="p-3 border-top">
        {{ $reports->links() }}
    </div>
    @endif
</div>
@endsection

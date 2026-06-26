@extends('admin.layouts.app')

@section('title', 'Report Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
        <h4 class="mb-1 fw-bold">Report #{{ $report->id }}</h4>
        <p class="text-muted mb-0">Submitted on {{ $report->created_at->format('M d, Y H:i') }}</p>
    </div>
    <div>
        @if($report->status === 'pending')
        <form action="{{ route('admin.reports.update_status', $report) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="investigating">
            <button type="submit" class="btn btn-info"><i class="bi bi-search me-1"></i> Investigate</button>
        </form>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Report Details --}}
        <div class="card-custom mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>Report Information
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Reason</div>
                    <div class="col-sm-8 fw-semibold">{{ $report->reason }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Description</div>
                    <div class="col-sm-8">{{ $report->description ?: 'No additional description provided.' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Reporter</div>
                    <div class="col-sm-8">
                        @if($report->reporter)
                        <a href="{{ route('admin.users.show', $report->reporter) }}">
                            {{ $report->reporter->full_name ?? $report->reporter->name }}
                        </a> ({{ $report->reporter->email }})
                        @else
                        Unknown
                        @endif
                    </div>
                </div>

                @if($report->evidence_url)
                <div class="mt-4">
                    <h6 class="fw-bold">Evidence Screenshot</h6>
                    <a href="{{ $report->evidence_url }}" target="_blank">
                        <img src="{{ $report->evidence_url }}" alt="Evidence" class="img-fluid rounded border" style="max-height: 400px">
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Target Details --}}
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="bi bi-bullseye me-2"></i>Reported Target ({{ ucfirst($report->report_type) }})
            </div>
            <div class="card-body">
                @if($report->report_type === 'job' && $report->job)
                    <h5>{{ $report->job->title }}</h5>
                    <p class="text-muted">{{ $report->job->employer_name }}</p>
                    <p>{{ Str::limit($report->job->description, 150) }}</p>
                    <a href="{{ route('admin.jobs.show', $report->job) }}" class="btn btn-outline-primary btn-sm">View Job</a>
                    <hr>
                    <h6>Take Action on Job</h6>
                    <form action="{{ route('admin.reports.suspend_job', $report->job) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to suspend this job?')">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-danger btn-sm">Suspend Job</button>
                    </form>

                @elseif($report->report_type === 'user' && $report->reported)
                    <h5>{{ $report->reported->full_name ?? $report->reported->name }}</h5>
                    <p class="text-muted">{{ $report->reported->email }} ({{ $report->reported->role }})</p>
                    <a href="{{ route('admin.users.show', $report->reported) }}" class="btn btn-outline-primary btn-sm">View User</a>
                    <hr>
                    <h6>Take Action on User</h6>
                    <form action="{{ route('admin.reports.suspend_user', $report->reported) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to suspend this user?')">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-danger btn-sm">Suspend User</button>
                    </form>

                @elseif($report->report_type === 'chat' && $report->chatMessage)
                    <div class="border p-3 rounded bg-light">
                        <strong>{{ $report->chatMessage->sender->name ?? 'Sender' }}:</strong><br>
                        {{ $report->chatMessage->message }}
                    </div>
                @else
                    <div class="alert alert-warning mb-0">Target entity could not be found. It may have been deleted.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Resolution Box --}}
        <div class="card-custom">
            <div class="card-header">
                <i class="bi bi-check2-circle me-2"></i>Resolve Report
            </div>
            <div class="card-body">
                <form action="{{ route('admin.reports.update_status', $report) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="investigating" {{ $report->status == 'investigating' ? 'selected' : '' }}>Investigating</option>
                            <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Resolved (Valid Report)</option>
                            <option value="dismissed" {{ $report->status == 'dismissed' ? 'selected' : '' }}>Dismissed (Invalid Report)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Admin Note (Audit Log)</label>
                        <textarea name="admin_note" class="form-control" rows="4" placeholder="Reason for your decision...">{{ $report->admin_note }}</textarea>
                        <div class="form-text">This note is for internal auditing purposes.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Report Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

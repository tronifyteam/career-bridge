@extends('admin.layouts.app')

@section('title', $job->title)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.jobs.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Jobs
    </a>
    {{-- Re-screen button --}}
    <form action="{{ route('admin.jobs.rescreen', $job) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i> Re-screen
        </button>
    </form>
</div>

<div class="row g-4">
    {{-- Job Details (left col) --}}
    <div class="col-md-8">
        <div class="card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="fw-bold mb-1">{{ $job->title }}</h4>
                    <div class="text-muted">
                        {{ $job->employer_name }} · {{ $job->location }}
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-role badge-status-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                    @if($job->is_urgent)
                        <span class="badge bg-danger">URGENT</span>
                    @endif
                </div>
            </div>

            {{-- M5: Risk Panel --}}
            @php
                $risk = $job->risk_level ?? 'low';
                $redFlags = $job->red_flags ?? [];
                $missingFields = $job->missing_fields ?? [];
                $riskConfig = [
                    'critical' => ['alert' => 'danger',  'icon' => 'bi-exclamation-triangle-fill', 'label' => 'CRITICAL'],
                    'high'     => ['alert' => 'warning',  'icon' => 'bi-exclamation-circle-fill', 'label' => 'HIGH RISK'],
                    'medium'   => ['alert' => 'info',     'icon' => 'bi-info-circle-fill', 'label' => 'MEDIUM RISK'],
                    'low'      => ['alert' => 'success',  'icon' => 'bi-shield-check-fill', 'label' => 'LOW RISK'],
                ];
                $rc = $riskConfig[$risk] ?? $riskConfig['low'];
            @endphp

            @if($risk !== 'low' || count($redFlags) > 0 || count($missingFields) > 0)
            <div class="alert alert-{{ $rc['alert'] }} py-3 px-4 mb-4" style="border-radius: 10px;">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi {{ $rc['icon'] }} me-2 fs-5"></i>
                    <strong>Auto-Screening Result: {{ $rc['label'] }}</strong>
                    @if($job->screened_at)
                        <small class="ms-auto text-muted">Screened {{ $job->screened_at->diffForHumans() }}</small>
                    @endif
                </div>

                @if(count($redFlags) > 0)
                <div class="mt-2">
                    <div class="fw-semibold small mb-1"><i class="bi bi-flag-fill me-1"></i>Red Flags ({{ count($redFlags) }})</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($redFlags as $flag)
                            <li>{{ $flag }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(count($missingFields) > 0)
                <div class="mt-2">
                    <div class="fw-semibold small mb-1"><i class="bi bi-dash-circle me-1"></i>Missing Fields ({{ count($missingFields) }})</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($missingFields as $field)
                            <span class="badge bg-light text-dark border small">{{ $field }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($redFlags) === 0 && count($missingFields) === 0)
                    <small class="mt-1 d-block">No specific flags detected by auto-screening.</small>
                @endif
            </div>
            @endif

            @if($job->status === 'rejected' && $job->rejection_reason)
                <div class="alert alert-secondary py-2 px-3 small mb-3">
                    <i class="bi bi-x-circle-fill me-2 text-danger"></i>
                    <strong>Alasan Penolakan:</strong> "{{ $job->rejection_reason }}"
                </div>
            @endif

            {{-- Job meta fields --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="small text-muted">Salary</div>
                    <div class="fw-semibold">{{ $job->salary }} / {{ $job->salary_period }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Category</div>
                    <div class="fw-semibold">{{ $job->category }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Employer Type</div>
                    <div class="fw-semibold">{{ str_replace('_', ' ', ucfirst($job->employer_type)) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Eligibility</div>
                    <div class="fw-semibold {{ empty($job->eligibility) || $job->eligibility === 'Unknown' ? 'text-danger' : '' }}">
                        {{ $job->eligibility ?: '—' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Employment Type</div>
                    <div class="fw-semibold">{{ $job->employment_type ?: '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Worker Count</div>
                    <div class="fw-semibold">{{ $job->worker_count ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Working Hours & Rest Days</div>
                    <div class="fw-semibold {{ empty($job->working_hours_and_rest_days) ? 'text-danger' : '' }}">
                        {{ $job->working_hours_and_rest_days ?: '— (Missing)' }}
                    </div>
                </div>
                @if($job->dormitory_meals_deductions)
                <div class="col-12">
                    <div class="small text-muted">Dormitory / Meals / Deductions</div>
                    <div class="fw-semibold">{{ $job->dormitory_meals_deductions }}</div>
                </div>
                @endif
            </div>

            @if($job->tags && count($job->tags) > 0)
            <div class="mb-4">
                @foreach($job->tags as $tag)
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1">{{ $tag }}</span>
                @endforeach
            </div>
            @endif

            @foreach(['description' => 'Description', 'duties' => 'Duties', 'requirements' => 'Requirements', 'benefits' => 'Benefits'] as $field => $label)
                @if($job->$field)
                <div class="mb-4">
                    <h6 class="fw-bold text-muted">{{ $label }}</h6>
                    <div class="small">{!! nl2br(e($job->$field)) !!}</div>
                </div>
                @endif
            @endforeach

            {{-- Agency proof documents --}}
            @if($job->employer_type === 'agency')
            <div class="mb-4 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-file-earmark-check me-1"></i>Agency Documents</h6>
                <div class="row g-2 small">
                    <div class="col-md-4">
                        <span class="text-muted">Employer Authorization: </span>
                        @if($job->employer_authorization_url)
                            <a href="{{ $job->employer_authorization_url }}" target="_blank" class="text-success"><i class="bi bi-check-circle me-1"></i>Uploaded</a>
                        @else
                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Missing</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Job Source Proof: </span>
                        @if($job->job_source_proof_url)
                            <a href="{{ $job->job_source_proof_url }}" target="_blank" class="text-success"><i class="bi bi-check-circle me-1"></i>Uploaded</a>
                        @else
                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Missing</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Fee Table: </span>
                        @if($job->fee_table_url)
                            <a href="{{ $job->fee_table_url }}" target="_blank" class="text-success"><i class="bi bi-check-circle me-1"></i>Uploaded</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <hr>
            <div class="text-muted small">
                Posted: {{ $job->posted_at?->format('M d, Y H:i') ?? 'N/A' }}
                @if($job->expires_at)
                    · Expires: {{ $job->expires_at->format('M d, Y') }}
                @endif
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-md-4">
        {{-- Job Moderation Panel --}}
        <div class="card-custom mb-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Job Moderation</h5>

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.jobs.updateStatus', $job) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label small text-muted">Job Status</label>
                    <select name="status" id="status-select" class="form-select">
                        <option value="draft" {{ $job->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted_for_review" {{ $job->status == 'submitted_for_review' ? 'selected' : '' }}>Submitted for Review</option>
                        <option value="published" {{ $job->status == 'published' ? 'selected' : '' }}>Published (Active)</option>
                        <option value="paused" {{ $job->status == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="closed" {{ $job->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="rejected" {{ $job->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="suspended" {{ $job->status == 'suspended' ? 'selected' : '' }}>Suspended (Pending Investigation)</option>
                    </select>
                </div>
                <div class="mb-3" id="reason_group" style="display: none;">
                    <label class="form-label small text-muted" id="reason_label">Alasan</label>
                    <textarea name="rejection_reason" class="form-control" rows="3"
                        placeholder="Sebutkan alasan...">{{ old('rejection_reason', $job->rejection_reason) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Update Job Status</button>
            </form>

            {{-- Quick action buttons --}}
            @if($job->status === 'submitted_for_review')
            <div class="mt-3 d-grid gap-2">
                <form action="{{ route('admin.jobs.updateStatus', $job) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="published">
                    <button type="submit" class="btn btn-success btn-sm w-100"
                        onclick="return confirm('Approve and publish this job?')">
                        <i class="bi bi-check-circle me-1"></i> Quick Approve
                    </button>
                </form>
                <form action="{{ route('admin.jobs.updateStatus', $job) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="suspended">
                    <input type="hidden" name="rejection_reason" value="Suspended pending further investigation.">
                    <button type="submit" class="btn btn-outline-warning btn-sm w-100"
                        onclick="return confirm('Suspend this job?')">
                        <i class="bi bi-pause-circle me-1"></i> Quick Suspend
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Applicants --}}
        <div class="card-custom">
            <div class="card-header">
                <i class="bi bi-people me-2"></i>Applicants ({{ $job->applications->count() }})
            </div>
            @forelse($job->applications as $app)
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold small">{{ $app->user?->full_name ?? 'Unknown' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $app->user?->email }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $app->user?->nationality ?? '' }}</div>
                    </div>
                    <span class="badge badge-role badge-status-{{ $app->status }}">{{ ucfirst($app->status) }}</span>
                </div>
                @if($app->cover_letter)
                <div class="mt-2 p-2 rounded small" style="background: #f8fafc;">
                    {{ Str::limit($app->cover_letter, 100) }}
                </div>
                @endif
                <div class="text-muted mt-1" style="font-size: 0.7rem;">
                    Applied: {{ $app->applied_at?->format('M d, Y') }}
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-muted small">No applicants yet</div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status-select');
        const reasonGroup  = document.getElementById('reason_group');
        const reasonLabel  = document.getElementById('reason_label');

        function toggleReason() {
            const val = statusSelect.value;
            if (val === 'rejected' || val === 'suspended') {
                reasonGroup.style.display = 'block';
                reasonLabel.textContent = val === 'suspended'
                    ? 'Alasan Penangguhan'
                    : 'Alasan Penolakan';
            } else {
                reasonGroup.style.display = 'none';
            }
        }

        statusSelect.addEventListener('change', toggleReason);
        toggleReason();
    });
</script>
@endsection

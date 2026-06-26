@extends('admin.layouts.app')

@section('title', $user->full_name ?? $user->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<div class="row g-4">
    {{-- User Info --}}
    <div class="col-md-4">
        <div class="card-custom p-4">
            <div class="text-center mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; color: var(--primary);">
                    {{ strtoupper(substr($user->full_name ?? $user->name, 0, 1)) }}
                </div>
                <h5 class="mt-3 mb-1 fw-bold">{{ $user->full_name ?? $user->name }}</h5>
                <span class="badge badge-role badge-{{ $user->role ?? 'worker' }}">{{ str_replace('_', ' ', ucfirst($user->role ?? 'No Role')) }}</span>
            </div>
            <hr>
            <dl class="row mb-0">
                <dt class="col-5 text-muted small">Email</dt>
                <dd class="col-7 small">{{ $user->email }}</dd>

                <dt class="col-5 text-muted small">Phone</dt>
                <dd class="col-7 small">{{ $user->phone ?? '—' }}</dd>

                @if($user->isEmployer())
                    <dt class="col-5 text-muted small">Company</dt>
                    <dd class="col-7 small">{{ $user->company_name ?? '—' }}</dd>
                    <dt class="col-5 text-muted small">Industry</dt>
                    <dd class="col-7 small">{{ $user->industry ?? '—' }}</dd>
                @else
                    <dt class="col-5 text-muted small">Nationality</dt>
                    <dd class="col-7 small">{{ $user->nationality ?? '—' }}</dd>
                    <dt class="col-5 text-muted small">City</dt>
                    <dd class="col-7 small">{{ $user->current_city ?? '—' }}</dd>
                @endif

                <dt class="col-5 text-muted small">Profile</dt>
                <dd class="col-7 small">
                    @if($user->profile_completed)
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Complete</span>
                    @else
                        <span class="text-warning"><i class="bi bi-exclamation-circle"></i> Incomplete</span>
                    @endif
                </dd>

                @if($user->isEmployer())
                    <dt class="col-5 text-muted small">Verification</dt>
                    <dd class="col-7 small">
                        <span class="badge badge-role badge-status-{{ $user->verification_status ?? 'unverified' }}">
                            {{ str_replace('_', ' ', ucfirst($user->verification_status ?? 'unverified')) }}
                        </span>
                    </dd>
                @endif

                @if($user->isWorker())
                    <dt class="col-5 text-muted small">Verified Badge</dt>
                    <dd class="col-7 small">
                        <span class="badge badge-role badge-status-{{ $user->verified_badge_status ?? 'unverified' }}">
                            {{ ucfirst($user->verified_badge_status ?? 'unverified') }}
                        </span>
                    </dd>
                    <dt class="col-5 text-muted small">Ready to Work</dt>
                    <dd class="col-7 small">
                        <span class="badge badge-role badge-status-{{ $user->ready_to_work_status ?? 'unverified' }}">
                            {{ ucfirst($user->ready_to_work_status ?? 'unverified') }}
                        </span>
                    </dd>
                    <dt class="col-5 text-muted small">Sponsorship</dt>
                    <dd class="col-7 small">{{ $user->sponsorship_status ?? '—' }}</dd>
                @endif

                <dt class="col-5 text-muted small">Joined</dt>
                <dd class="col-7 small">{{ $user->created_at?->format('M d, Y H:i') }}</dd>
            </dl>
        </div>

        {{-- Verification Panel (for Employer) --}}
        @if($user->isEmployer())
        <div class="card-custom mt-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Verification</h5>
            <form action="{{ route('admin.users.updateVerification', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label small text-muted">Verification Status</label>
                    <select name="verification_status" class="form-select">
                        <option value="unverified" {{ $user->verification_status == 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="pending" {{ $user->verification_status == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="basic_verified" {{ $user->verification_status == 'basic_verified' ? 'selected' : '' }}>Basic Verified</option>
                        <option value="manually_verified" {{ $user->verification_status == 'manually_verified' ? 'selected' : '' }}>Manually Verified</option>
                        <option value="rejected" {{ $user->verification_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <label class="form-label small text-muted mt-3">Admin Note (Optional)</label>
                    <textarea name="verification_note" class="form-control mb-3" rows="2" placeholder="e.g. Please provide a clearer copy of your license.">{{ $user->verification_note }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
            </form>
        </div>
        @endif

        {{-- Verification Panel (for Worker) --}}
        @if($user->isWorker())
        <div class="card-custom mt-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Worker Verification</h5>
            <form action="{{ route('admin.users.updateWorkerVerification', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label small text-muted">Verified Badge Status</label>
                    <select name="verified_badge_status" class="form-select mb-3">
                        <option value="unverified" {{ ($user->verified_badge_status ?? 'unverified') == 'unverified' ? 'selected' : '' }}>Unverified (Belum Verifikasi)</option>
                        <option value="pending" {{ ($user->verified_badge_status ?? 'unverified') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="verified" {{ ($user->verified_badge_status ?? 'unverified') == 'verified' ? 'selected' : '' }}>Approved (Terverifikasi)</option>
                        <option value="rejected" {{ ($user->verified_badge_status ?? 'unverified') == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    </select>

                    <label class="form-label small text-muted">Ready to Work Status</label>
                    <select name="ready_to_work_status" class="form-select mb-3">
                        <option value="not_ready" {{ ($user->ready_to_work_status ?? 'not_ready') == 'not_ready' ? 'selected' : '' }}>Not Ready / Unverified</option>
                        <option value="pending" {{ ($user->ready_to_work_status ?? 'not_ready') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="ready" {{ ($user->ready_to_work_status ?? 'not_ready') == 'ready' ? 'selected' : '' }}>Approved (Ready to Work)</option>
                        <option value="rejected" {{ ($user->ready_to_work_status ?? 'not_ready') == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    </select>

                    <label class="form-label small text-muted">Sponsorship Status</label>
                    <input type="text" name="sponsorship_status" class="form-control mb-3" value="{{ $user->sponsorship_status }}" placeholder="e.g. Ready / Sponsorship needed">

                    <label class="form-label small text-muted">Admin Note (Optional)</label>
                    <textarea name="verification_note" class="form-control mb-3" rows="2" placeholder="e.g. Berkas buram, silakan unggah ulang.">{{ $user->verification_note }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Update Worker Verification</button>
            </form>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Verification Documents (if employer) --}}
        @if($user->isEmployer())
        <div class="card-custom mb-4">
            <div class="card-header"><i class="bi bi-file-earmark-check me-2"></i>Verification Documents ({{ $user->documents->count() }})</div>
            @if($user->documents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->documents as $doc)
                        <tr>
                            <td class="fw-semibold small">
                                @if($doc->document_type === 'company_registration')
                                    Company Registration
                                @elseif($doc->document_type === 'factory_permit')
                                    Factory Operating Permit
                                @elseif($doc->document_type === 'agency_license')
                                    Agency License (License Number: {{ $user->license_number ?? 'N/A' }})
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                @endif
                                @if($doc->review_note)
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-chat-left-text me-1"></i> Note: {{ $doc->review_note }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-role badge-status-{{ $doc->status }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $doc->created_at?->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <a href="{{ $doc->document_url }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>View File
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#reviewForm-{{ $doc->id }}">
                                        <i class="bi bi-pencil-square me-1"></i>Review
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr id="reviewForm-{{ $doc->id }}" class="collapse bg-light">
                            <td colspan="4" class="p-3">
                                <div class="card card-body border-0 shadow-sm p-3 bg-white">
                                    <h6 class="fw-bold mb-3">Review Document: {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</h6>
                                    <div class="d-flex flex-wrap gap-4">
                                        {{-- Approve form --}}
                                        <form action="{{ route('admin.employers.approveDocument', $doc->id) }}" method="POST" class="flex-grow-1" style="min-width: 250px;">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-2">
                                                <label class="form-label small text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Approve Document</label>
                                                <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional approval note (e.g. Looks good)">
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                Approve
                                            </button>
                                        </form>

                                        {{-- Reject form --}}
                                        <form action="{{ route('admin.employers.rejectDocument', $doc->id) }}" method="POST" class="flex-grow-1" style="min-width: 250px;">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-2">
                                                <label class="form-label small text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i> Reject Document</label>
                                                <input type="text" name="note" class="form-control form-control-sm" placeholder="Rejection reason (required)" required>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-danger w-100">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-4 text-center text-muted small">
                No verification documents uploaded yet.
            </div>
            @endif
        </div>
        @endif

        {{-- Verification Documents (if worker) --}}
        @if($user->isWorker())
        <div class="card-custom mb-4">
            <div class="card-header"><i class="bi bi-file-earmark-check me-2"></i>Worker Verification Documents ({{ $user->workerDocuments->count() }})</div>
            @if($user->workerDocuments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->workerDocuments as $doc)
                        <tr>
                            <td class="fw-semibold small">
                                @if($doc->document_type === 'selfie')
                                    Selfie Foto
                                @elseif($doc->document_type === 'personal_document')
                                    Personal Document (KTP/Passport)
                                @elseif($doc->document_type === 'student_work_permit')
                                    Student Work Permit
                                @elseif($doc->document_type === 'transfer_document')
                                    Transfer Document
                                @elseif($doc->document_type === 'contract_ending_proof')
                                    Contract Ending Proof
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-role badge-status-{{ $doc->status }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $doc->created_at?->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ $doc->document_url }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View File
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-4 text-center text-muted small">
                No verification documents uploaded yet.
            </div>
            @endif
        </div>
        @endif

        {{-- Jobs (if employer) --}}
        @if($user->isEmployer() && $user->jobs->count() > 0)
        <div class="card-custom mb-4">
            <div class="card-header"><i class="bi bi-briefcase me-2"></i>Posted Jobs ({{ $user->jobs->count() }})</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th>Location</th><th>Status</th><th>Posted</th></tr></thead>
                    <tbody>
                        @foreach($user->jobs as $job)
                        <tr>
                            <td><a href="{{ route('admin.jobs.show', $job) }}" class="text-decoration-none fw-semibold">{{ $job->title }}</a></td>
                            <td class="small">{{ $job->location }}</td>
                            <td><span class="badge badge-role badge-status-{{ $job->status }}">{{ ucfirst($job->status) }}</span></td>
                            <td class="small text-muted">{{ $job->posted_at?->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Applications (if worker) --}}
        @if($user->applications->count() > 0)
        <div class="card-custom">
            <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i>Applications ({{ $user->applications->count() }})</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Job</th><th>Status</th><th>Applied</th></tr></thead>
                    <tbody>
                        @foreach($user->applications as $app)
                        <tr>
                            <td class="small fw-semibold">{{ $app->job?->title ?? 'Unknown' }}</td>
                            <td><span class="badge badge-role badge-status-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                            <td class="small text-muted">{{ $app->applied_at?->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

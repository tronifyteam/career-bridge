@extends('admin.layouts.app')

@section('title', ($agency->company_name ?? $agency->full_name ?? $agency->name) . ' — Employer')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.agencies.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Employers
    </a>
</div>

@php
    $roleColors = [
        'company'     => 'primary',
        'factory'     => 'warning',
        'family_care' => 'danger',
        'agency'      => 'info',
        'agency_staff'=> 'secondary',
    ];
    $roleIcons = [
        'company'     => 'bi-building',
        'factory'     => 'bi-gear-fill',
        'family_care' => 'bi-house-heart-fill',
        'agency'      => 'bi-people-fill',
        'agency_staff'=> 'bi-person-badge',
    ];
    $roleColor = $roleColors[$agency->role] ?? 'secondary';
    $roleIcon  = $roleIcons[$agency->role]  ?? 'bi-building';
@endphp

<div class="row g-4">

    {{-- ── LEFT COLUMN ─────────────────────────────────────────────── --}}
    <div class="col-md-4">

        {{-- Profile Card --}}
        <div class="card-custom p-4 mb-4">
            <div class="text-center mb-3">
                @if($agency->avatar_url)
                    <img src="{{ $agency->avatar_url }}" alt="Logo"
                         class="rounded-3 border shadow-sm"
                         style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-3 bg-{{ $roleColor }} bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                         style="width:90px;height:90px;font-size:2.5rem;color:var(--bs-{{ $roleColor }});">
                        <i class="bi {{ $roleIcon }}"></i>
                    </div>
                @endif
                <h5 class="mt-3 mb-1 fw-bold">{{ $agency->company_name ?? $agency->full_name ?? $agency->name }}</h5>
                <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} border border-{{ $roleColor }}-subtle">
                    {{ str_replace('_', ' ', ucfirst($agency->role ?? 'Employer')) }}
                </span>
            </div>
            <hr>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">PIC Name</dt>
                <dd class="col-7">{{ $agency->full_name ?? $agency->name }}</dd>

                <dt class="col-5 text-muted">Email</dt>
                <dd class="col-7" style="word-break:break-all;">{{ $agency->email }}</dd>

                <dt class="col-5 text-muted">Phone</dt>
                <dd class="col-7">{{ $agency->phone ?? '—' }}</dd>

                @if($agency->unified_business_number)
                <dt class="col-5 text-muted">UBN</dt>
                <dd class="col-7 font-monospace">{{ $agency->unified_business_number }}</dd>
                @endif

                @if($agency->license_number)
                <dt class="col-5 text-muted">License No.</dt>
                <dd class="col-7 font-monospace">{{ $agency->license_number }}</dd>
                @endif

                @if($agency->industry)
                <dt class="col-5 text-muted">Industry</dt>
                <dd class="col-7">{{ $agency->industry }}</dd>
                @endif

                <dt class="col-5 text-muted">Profile</dt>
                <dd class="col-7">
                    @if($agency->profile_completed)
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Complete</span>
                    @else
                        <span class="text-warning"><i class="bi bi-exclamation-circle"></i> Incomplete</span>
                    @endif
                </dd>

                <dt class="col-5 text-muted">Joined</dt>
                <dd class="col-7">{{ $agency->created_at?->format('M d, Y') }}</dd>

                <dt class="col-5 text-muted">Jobs Posted</dt>
                <dd class="col-7">{{ $agency->jobs()->count() }}</dd>
            </dl>
        </div>

        {{-- Verification Status Card --}}
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-shield-check me-2 text-success"></i>Verification Status
            </div>
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-semibold text-muted">Account Status</span>
                    @php $vs = $agency->verification_status ?? 'unverified'; @endphp
                    <span class="badge badge-role badge-status-{{ $vs }} fs-6 px-3 py-2">
                        {{ str_replace('_', ' ', ucfirst($vs)) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-semibold text-muted">Verified Badge</span>
                    @php $badge = $agency->verified_badge_status ?? 'unverified'; @endphp
                    <span class="badge badge-role badge-status-{{ $badge }} fs-6 px-3 py-2">
                        @if($badge === 'verified') <i class="bi bi-patch-check-fill me-1"></i>
                        @elseif($badge === 'pending') <i class="bi bi-hourglass-split me-1"></i>
                        @elseif($badge === 'rejected') <i class="bi bi-x-circle me-1"></i>
                        @endif
                        {{ ucfirst($badge) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold text-muted">Can Post Jobs</span>
                    @if($agency->isVerifiedEmployer())
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Yes</span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>No</span>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="p-3 border-top">
                <p class="small fw-semibold text-muted mb-2">Quick Actions</p>
                <div class="d-flex gap-2 flex-wrap">
                    @if($vs !== 'basic_verified' && $vs !== 'manually_verified')
                    <button class="btn btn-sm btn-success flex-fill"
                            onclick="approveAgency({{ $agency->id }})">
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                    @endif
                    @if($vs !== 'rejected')
                    <button class="btn btn-sm btn-outline-danger flex-fill"
                            onclick="openRejectModal('employer', {{ $agency->id }})">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    @endif
                </div>
                @if($vs !== 'suspended')
                <button class="btn btn-sm btn-outline-warning w-100 mt-2"
                        onclick="openSuspendModal({{ $agency->id }})">
                    <i class="bi bi-slash-circle me-1"></i>Suspend Agency
                </button>
                @else
                <div class="alert alert-warning py-2 mt-2 mb-0 small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Account Suspended</strong>
                    <button class="btn btn-sm btn-outline-success ms-2" onclick="approveAgency({{ $agency->id }})">Restore</button>
                </div>
                @endif
            </div>
        </div>

        {{-- Change Password --}}
        @include('admin.users.partials.change_password', ['user' => $agency])

        {{-- Jobs Summary --}}
        @php $recentJobs = $agency->jobs()->latest()->take(5)->get(); @endphp
        @if($recentJobs->isNotEmpty())
        <div class="card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-briefcase me-2 text-primary"></i>Recent Jobs
            </div>
            <div class="p-0">
                @foreach($recentJobs as $job)
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold">{{ Str::limit($job->title ?? 'Untitled', 30) }}</div>
                        <div class="small text-muted">{{ $job->created_at?->format('M d, Y') }}</div>
                    </div>
                    <span class="badge badge-role badge-status-{{ $job->status ?? 'draft' }}">{{ ucfirst($job->status ?? 'draft') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── RIGHT COLUMN ────────────────────────────────────────────── --}}
    <div class="col-md-8">

        {{-- Employer Documents --}}
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-check me-2 text-success"></i>Uploaded Documents</span>
                <span class="badge bg-secondary">{{ $documents->count() }} doc{{ $documents->count() !== 1 ? 's' : '' }}</span>
            </div>

            @if($documents->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-file-earmark-x display-5 d-block mb-3 opacity-25"></i>
                    <p class="mb-0">No documents uploaded yet.</p>
                    <small>Employer needs to upload required documents from the mobile app.</small>
                </div>
            @else
                <div class="p-3">
                    @foreach($documents as $doc)
                    <div class="border rounded-3 p-3 mb-3 bg-light" id="emp-doc-card-{{ $doc->id }}">
                        <div class="row align-items-center g-3">
                            {{-- File Preview --}}
                            <div class="col-md-2 text-center">
                                @php
                                    $ext = strtolower(pathinfo($doc->document_url ?? '', PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                @endphp
                                @if($isImage && $doc->document_url)
                                    <img src="{{ $doc->document_url }}" alt="doc"
                                         class="img-fluid rounded-2 shadow-sm border"
                                         style="max-height:80px;object-fit:cover;cursor:pointer"
                                         onclick="openImageModal('{{ $doc->document_url }}', '{{ $doc->document_type ?? 'Document' }}')">
                                @elseif($doc->document_url)
                                    <a href="{{ $doc->document_url }}" target="_blank"
                                       class="d-block text-center text-muted">
                                        <i class="bi bi-file-earmark-pdf display-6"></i>
                                        <div class="small mt-1">{{ strtoupper($ext) ?: 'FILE' }}</div>
                                    </a>
                                @else
                                    <i class="bi bi-file-earmark text-muted" style="font-size:2.5rem;"></i>
                                @endif
                            </div>

                            {{-- Doc Info --}}
                            <div class="col-md-5">
                                <div class="fw-semibold text-capitalize">
                                    {{ str_replace('_', ' ', $doc->document_type ?? 'Unknown') }}
                                </div>
                                <div class="small text-muted">
                                    Uploaded {{ $doc->created_at?->diffForHumans() }}
                                </div>
                                @if($doc->review_note)
                                <div class="small mt-1 text-info">
                                    <i class="bi bi-chat-square-text me-1"></i>{{ $doc->review_note }}
                                </div>
                                @endif
                                @if($doc->reviewed_at)
                                <div class="small text-muted">
                                    Reviewed {{ $doc->reviewed_at?->format('M d, Y') }}
                                    @if($doc->reviewed_by) by Admin @endif
                                </div>
                                @endif
                            </div>

                            {{-- Status + Actions --}}
                            <div class="col-md-5 text-end">
                                @php $ds = $doc->status ?? 'pending'; @endphp
                                <div class="mb-2">
                                    <span class="badge badge-role badge-status-{{ $ds }} fs-6" id="emp-doc-badge-{{ $doc->id }}">
                                        @if($ds === 'approved') <i class="bi bi-check-circle-fill me-1"></i>Approved
                                        @elseif($ds === 'rejected') <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                        @elseif($ds === 'pending') <i class="bi bi-hourglass-split me-1"></i>Pending
                                        @else {{ ucfirst($ds) }}
                                        @endif
                                    </span>
                                </div>
                                @if($doc->document_url)
                                <a href="{{ $doc->document_url }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-download"></i>
                                </a>
                                @endif
                                @if($ds !== 'approved')
                                <button class="btn btn-sm btn-success me-1"
                                        onclick="approveEmpDoc({{ $doc->id }})">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                @endif
                                @if($ds !== 'rejected')
                                <button class="btn btn-sm btn-danger"
                                        onclick="openRejectModal('emp_doc', {{ $doc->id }})">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Bulk approve if all pending --}}
                    @if($documents->filter(fn($d) => ($d->status ?? 'pending') !== 'approved')->count() > 0)
                    <div class="text-end mt-2">
                        <button class="btn btn-success" onclick="approveAllDocs()">
                            <i class="bi bi-check-all me-1"></i>Approve All Documents
                        </button>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Verification Log --}}
        @if(isset($logs) && $logs->isNotEmpty())
        <div class="card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-clock-history me-2 text-primary"></i>Verification Activity Log
            </div>
            <div style="max-height: 280px; overflow-y: auto;">
                @foreach($logs as $log)
                <div class="d-flex gap-3 align-items-start px-3 py-2 border-bottom">
                    <div class="mt-1">
                        @if(in_array($log->action ?? '', ['approved','verified']))
                            <span class="badge bg-success rounded-circle p-1"><i class="bi bi-check-lg"></i></span>
                        @elseif($log->action === 'rejected')
                            <span class="badge bg-danger rounded-circle p-1"><i class="bi bi-x-lg"></i></span>
                        @elseif($log->action === 'suspended')
                            <span class="badge bg-warning text-dark rounded-circle p-1"><i class="bi bi-pause-fill"></i></span>
                        @else
                            <span class="badge bg-secondary rounded-circle p-1"><i class="bi bi-pencil"></i></span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">{{ ucfirst(str_replace('_', ' ', $log->action ?? 'action')) }}</div>
                        @if($log->note)
                            <div class="small text-muted">{{ $log->note }}</div>
                        @endif
                        <div class="small text-muted opacity-75">
                            by {{ $log->verifiedBy?->name ?? 'System' }} ·
                            {{ $log->verified_at?->format('M d, Y H:i') }}
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border small">{{ ucfirst($log->entity_type ?? '') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Agency Staff --}}
@if(isset($staffMembers) && $agency->role === 'agency')
<div class="card-custom mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people-fill me-2 text-primary"></i>Agency Staff Members</span>
        <span class="badge bg-secondary">{{ $staffMembers->count() }} staff</span>
    </div>
    @if($staffMembers->isEmpty())
        <div class="p-4 text-center text-muted">
            <p class="mb-0">This agency does not have any staff members yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Staff Name</th>
                        <th>Email / Phone</th>
                        <th>Verification</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $staff)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                @if($staff->user->avatar_url)
                                    <img src="{{ $staff->user->avatar_url }}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex justify-content-center align-items-center text-secondary" style="width:32px;height:32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $staff->user->full_name ?? $staff->user->name }}</div>
                                    <div class="small text-muted">{{ $staff->position ?? 'Staff' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="small">
                            <div>{{ $staff->user->email }}</div>
                            <div class="text-muted">{{ $staff->user->phone ?? '—' }}</div>
                        </td>
                        <td>
                            @php $vs = $staff->user->verification_status ?? 'unverified'; @endphp
                            <span class="badge badge-role badge-status-{{ $vs }}">
                                {{ str_replace('_', ' ', ucfirst($vs)) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $staff->user->created_at?->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.agencies.show', $staff->user_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif

{{-- ── Image Lightbox Modal ──────────────────────────────────────── --}}
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-semibold" id="imageModalLabel">Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="imageModalSrc" src="" alt="" class="img-fluid rounded-3" style="max-height: 75vh;">
            </div>
        </div>
    </div>
</div>

{{-- ── Reject Note Modal ────────────────────────────────────────── --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-semibold text-danger">
                    <i class="bi bi-x-circle me-2"></i><span id="rejectModalTitle">Reject</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                <textarea id="rejectNote" class="form-control" rows="3"
                          placeholder="e.g. Dokumen tidak jelas / Tidak sesuai / Informasi tidak valid..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="rejectConfirmBtn">
                    <i class="bi bi-x-circle me-1"></i>Confirm Reject
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Suspend Confirm Modal ──────────────────────────────────── --}}
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-semibold text-warning">
                    <i class="bi bi-slash-circle me-2"></i>Suspend Agency
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Suspending will pause all active job postings from this employer.</p>
                <label class="form-label">Reason (optional)</label>
                <textarea id="suspendNote" class="form-control" rows="2"
                          placeholder="Reason for suspension..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="suspendConfirmBtn">
                    <i class="bi bi-slash-circle me-1"></i>Suspend
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const AGENCY_ID = {{ $agency->id }};

    // ── Helpers ────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-floating alert-dismissible fade show`;
        el.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    async function apiCall(url, method, body = {}) {
        const r = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        return r.json();
    }

    function openImageModal(src, label) {
        document.getElementById('imageModalSrc').src = src;
        document.getElementById('imageModalLabel').textContent = label;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // ── Reject Modal ───────────────────────────────────────────────────
    let _rejectType = null;
    let _rejectId   = null;

    function openRejectModal(type, id) {
        _rejectType = type;
        _rejectId   = id;
        const Titles = { agency: 'Reject Agency Account', emp_doc: 'Reject Document' };
        document.getElementById('rejectModalTitle').textContent = titles[type] ?? 'Reject';
        document.getElementById('rejectNote').value = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    document.getElementById('rejectConfirmBtn').addEventListener('click', async () => {
        const note = document.getElementById('rejectNote').value.trim();
        if (!note) { alert('Please provide a rejection reason.'); return; }

        let url;
        if (_rejectType === 'employer') {
            url = `/admin.agencies.${_rejectId}/reject`;
        } else {
            url = `/admin.agencies.documents/${_rejectId}/reject`;
        }

        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        const res = await apiCall(url, 'PUT', { note });
        if (res.success) { showToast(res.message ?? 'Rejected.'); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    });

    // ── Approve Employer ───────────────────────────────────────────────
    async function approveAgency(id) {
        if (!confirm('Approve this employer? They will be able to post jobs.')) return;
        const res = await apiCall(`/admin.agencies.${id}/approve`, 'PUT');
        if (res.success) { showToast(res.message); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    }

    // ── Approve Employer Document ──────────────────────────────────────
    async function approveEmpDoc(docId) {
        if (!confirm('Approve this document?')) return;
        const res = await apiCall(`/admin.agencies.documents/${docId}/approve`, 'PUT', { note: '' });
        if (res.success) {
            showToast('Document approved.');
            setTimeout(() => location.reload(), 1200);
        } else showToast(res.message ?? 'Error', 'danger');
    }

    async function approveAllDocs() {
        if (!confirm('Approve all pending documents?')) return;
        const pendingBtns = document.querySelectorAll('[onclick^="approveEmpDoc"]');
        let last;
        for (const btn of pendingBtns) {
            const id = btn.getAttribute('onclick').match(/\d+/)?.[0];
            if (id) last = await apiCall(`/admin.agencies.documents/${id}/approve`, 'PUT', { note: '' });
        }
        showToast('All documents approved.');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Suspend Agency ───────────────────────────────────────────────
    function openSuspendModal(id) {
        _rejectId = id;
        document.getElementById('suspendNote').value = '';
        new bootstrap.Modal(document.getElementById('suspendModal')).show();
    }

    document.getElementById('suspendConfirmBtn').addEventListener('click', async () => {
        const note = document.getElementById('suspendNote').value.trim();
        bootstrap.Modal.getInstance(document.getElementById('suspendModal')).hide();
        const res = await apiCall(`/admin.agencies.${_rejectId}/suspend`, 'PUT', { note });
        if (res.success) { showToast(res.message ?? 'Employer suspended.'); setTimeout(() => location.reload(), 1500); }
        else showToast(res.message ?? 'Error', 'danger');
    });
</script>
@endsection


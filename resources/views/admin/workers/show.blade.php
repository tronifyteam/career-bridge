@extends('admin.layouts.app')

@section('title', 'Worker — ' . ($user->full_name ?? $user->name))

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.workers.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Workers
    </a>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN: Profile Info ─────────────────────────────────── --}}
    <div class="col-md-4">

        {{-- Profile Card --}}
        <div class="card-custom p-4 mb-4">
            <div class="text-center mb-3">
                @if($user->selfie_file_url)
                    <img src="{{ $user->selfie_file_url }}" alt="Selfie"
                         class="rounded-circle border border-3 shadow-sm"
                         style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                         style="width:90px;height:90px;font-size:2.5rem;color:var(--primary);">
                        {{ strtoupper(substr($user->full_name ?? $user->name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="mt-3 mb-1 fw-bold">{{ $user->full_name ?? $user->name }}</h5>
                <span class="badge badge-role badge-worker">Worker</span>
                @if($user->nationality)
                    <div class="text-muted small mt-1">🌏 {{ $user->nationality }}</div>
                @endif
            </div>
            <hr>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Email</dt>
                <dd class="col-7">{{ $user->email }}</dd>

                <dt class="col-5 text-muted">Phone</dt>
                <dd class="col-7">{{ $user->phone ?? '—' }}</dd>

                <dt class="col-5 text-muted">City</dt>
                <dd class="col-7">{{ $user->current_city ?? '—' }}</dd>

                <dt class="col-5 text-muted">Profile</dt>
                <dd class="col-7">
                    @if($user->profile_completed)
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Complete</span>
                    @else
                        <span class="text-warning"><i class="bi bi-exclamation-circle"></i> Incomplete</span>
                    @endif
                </dd>

                <dt class="col-5 text-muted">Joined</dt>
                <dd class="col-7">{{ $user->created_at?->format('M d, Y') }}</dd>
            </dl>
        </div>

        {{-- Badge Status Card --}}
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-patch-check me-2 text-primary"></i>Badge Status
            </div>
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-semibold text-muted">Verified Badge</span>
                    @php $badge = $user->verified_badge_status ?? 'unverified'; @endphp
                    <span class="badge badge-role badge-status-{{ $badge }} fs-6 px-3 py-2">
                        @if($badge === 'verified') <i class="bi bi-patch-check-fill me-1"></i>
                        @elseif($badge === 'pending') <i class="bi bi-hourglass-split me-1"></i>
                        @elseif($badge === 'rejected') <i class="bi bi-x-circle me-1"></i>
                        @else <i class="bi bi-dash-circle me-1"></i>
                        @endif
                        {{ ucfirst($badge) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-semibold text-muted">Ready to Work</span>
                    @php $rtw = $user->ready_to_work_status ?? 'not_ready'; @endphp
                    <span class="badge badge-role badge-status-{{ $rtw === 'ready' ? 'approved' : ($rtw === 'pending' ? 'pending' : ($rtw === 'rejected' ? 'rejected' : 'unverified')) }} fs-6 px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $rtw)) }}
                    </span>
                </div>
                @if($user->sponsorship_status)
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold text-muted">Sponsorship</span>
                    <span class="badge bg-secondary">{{ ucfirst($user->sponsorship_status) }}</span>
                </div>
                @endif
            </div>

            {{-- Badge Override Form --}}
            <div class="p-3 border-top bg-light">
                <p class="small fw-semibold text-muted mb-2"><i class="bi bi-sliders me-1"></i>Manual Override</p>
                <form id="form-badge-override"
                      action="{{ route('admin.workers.show', $user) }}"
                      method="POST" data-worker-id="{{ $user->id }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_action" value="override_badge">
                    <div class="mb-2">
                        <label class="form-label small">Verified Badge</label>
                        <select name="verified_badge_status" class="form-select form-select-sm">
                            <option value="">— No change —</option>
                            @foreach(['unverified','pending','verified','rejected'] as $s)
                                <option value="{{ $s }}" {{ $user->verified_badge_status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Ready to Work</label>
                        <select name="ready_to_work_status" class="form-select form-select-sm">
                            <option value="">— No change —</option>
                            @foreach(['not_ready','pending','ready','rejected'] as $s)
                                <option value="{{ $s }}" {{ $user->ready_to_work_status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Admin Note</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Reason (optional)">
                    </div>
                    <button type="button" class="btn btn-sm btn-warning w-100"
                            onclick="submitBadgeOverride()">
                        <i class="bi bi-sliders me-1"></i>Apply Override
                    </button>
                </form>
            </div>
        </div>

        {{-- Suspend / Restore --}}
        <div class="card-custom">
            <div class="card-header fw-semibold text-danger">
                <i class="bi bi-shield-exclamation me-2"></i>Moderation
            </div>
            <div class="p-3 d-flex gap-2">
                @if(($user->verification_status ?? '') !== 'suspended')
                <button class="btn btn-sm btn-outline-danger flex-fill"
                        onclick="confirmSuspendUser({{ $user->id }})">
                    <i class="bi bi-slash-circle me-1"></i>Suspend User
                </button>
                @else
                <button class="btn btn-sm btn-outline-success flex-fill"
                        onclick="restoreUser({{ $user->id }})">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore User
                </button>
                @endif
            </div>
        </div>

        {{-- Change Password --}}
        @include('admin.users.partials.change_password')
    </div>

    {{-- ── RIGHT COLUMN: Selfie + Documents + Logs ──────────────────── --}}
    <div class="col-md-8">

        {{-- Selfie Review --}}
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-camera me-2 text-primary"></i>Selfie / Face Verification
            </div>
            <div class="p-4">
                @if($user->selfie_file_url)
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ $user->selfie_file_url }}" alt="Selfie"
                                 class="img-fluid rounded-3 shadow border"
                                 style="max-height:220px;object-fit:cover;cursor:pointer"
                                 onclick="openImageModal('{{ $user->selfie_file_url }}', 'Selfie — {{ $user->full_name ?? $user->name }}')">
                            <div class="mt-2">
                                <a href="{{ $user->selfie_file_url }}" target="_blank" class="small text-muted">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Open full size
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                @if($user->selfie_verified_at)
                                    <div class="alert alert-success py-2 mb-2">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>Selfie Approved</strong> on {{ $user->selfie_verified_at->format('M d, Y H:i') }}
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Selfie pending review</strong> — uploaded but not yet approved.
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                @if(!$user->selfie_verified_at)
                                    <button class="btn btn-success"
                                            onclick="approveSelfie({{ $user->id }})">
                                        <i class="bi bi-check-circle me-1"></i>Approve Selfie
                                    </button>
                                @endif
                                <button class="btn btn-outline-danger"
                                        onclick="rejectSelfie({{ $user->id }})">
                                    <i class="bi bi-x-circle me-1"></i>Reject Selfie
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-camera-video-off display-5 d-block mb-3 opacity-25"></i>
                        <p class="mb-0">No selfie uploaded yet.</p>
                        <small>Worker needs to upload a selfie from the mobile app.</small>
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents Review --}}
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-check me-2 text-primary"></i>Uploaded Documents</span>
                <div>
                    @if($documents->filter(fn($d) => ($d->review_status ?? 'pending') !== 'approved')->count() > 0 || ($user->selfie_file_url && !$user->selfie_verified_at))
                        <button class="btn btn-sm btn-success me-2" onclick="approveAllDocsAndSelfie({{ $user->id }})">
                            <i class="bi bi-check-all me-1"></i>Approve All
                        </button>
                    @endif
                    <span class="badge bg-secondary">{{ $documents->count() }} doc{{ $documents->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            @if($documents->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-file-earmark-x display-5 d-block mb-3 opacity-25"></i>
                    <p class="mb-0">No documents uploaded yet.</p>
                </div>
            @else
                <div class="p-3">
                    @foreach($documents as $doc)
                    <div class="border rounded-3 p-3 mb-3 bg-light position-relative doc-card"
                         id="doc-card-{{ $doc->id }}">
                        <div class="row align-items-center g-3">
                            {{-- File Preview --}}
                            <div class="col-md-2 text-center">
                                @php
                                    $ext = strtolower(pathinfo($doc->file_url ?? '', PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                @endphp
                                @if($isImage && $doc->file_url)
                                    <img src="{{ $doc->file_url }}" alt="doc"
                                         class="img-fluid rounded-2 shadow-sm border"
                                         style="max-height:80px;object-fit:cover;cursor:pointer"
                                         onclick="openImageModal('{{ $doc->file_url }}', '{{ $doc->documentType?->document_type_name ?? 'Document' }}')">
                                @elseif($doc->file_url)
                                    <a href="{{ $doc->file_url }}" target="_blank"
                                       class="d-block text-center text-muted">
                                        <i class="bi bi-file-earmark-pdf display-6"></i>
                                        <div class="small mt-1">{{ strtoupper($ext) }}</div>
                                    </a>
                                @else
                                    <i class="bi bi-file-earmark text-muted" style="font-size:2.5rem;"></i>
                                @endif
                            </div>

                            {{-- Doc Info --}}
                            <div class="col-md-5">
                                <div class="fw-semibold">
                                    {{ $doc->documentType?->document_type_name ?? 'Unknown Document' }}
                                </div>
                                <div class="small text-muted">
                                    {{ $doc->original_filename ?? basename($doc->file_url ?? '') }}
                                </div>
                                <div class="small text-muted">
                                    Uploaded {{ $doc->created_at?->diffForHumans() }}
                                </div>
                                @if($doc->review_note)
                                <div class="small mt-1 text-info">
                                    <i class="bi bi-chat-square-text me-1"></i>{{ $doc->review_note }}
                                </div>
                                @endif
                            </div>

                            {{-- Status + Actions --}}
                            <div class="col-md-5 text-end">
                                <div class="mb-2">
                                    @php $ds = $doc->review_status ?? 'pending'; @endphp
                                    <span class="badge badge-role badge-status-{{ $ds }} fs-6" id="doc-status-badge-{{ $doc->id }}">
                                        @if($ds === 'approved') <i class="bi bi-check-circle-fill me-1"></i>Approved
                                        @elseif($ds === 'rejected') <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                        @elseif($ds === 'pending') <i class="bi bi-hourglass-split me-1"></i>Pending
                                        @else {{ ucfirst($ds) }}
                                        @endif
                                    </span>
                                </div>
                                @if($doc->reviewed_at)
                                <div class="small text-muted mb-2">
                                    Reviewed {{ $doc->reviewed_at?->format('M d, Y') }}
                                </div>
                                @endif
                                @if($doc->file_url)
                                <a href="{{ $doc->file_url }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-download"></i>
                                </a>
                                @endif
                                @if($ds !== 'approved')
                                <button class="btn btn-sm btn-success me-1"
                                        onclick="approveDocument({{ $user->id }}, {{ $doc->id }})">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                @endif
                                @if($ds !== 'rejected')
                                <button class="btn btn-sm btn-danger"
                                        onclick="rejectDocument({{ $user->id }}, {{ $doc->id }})">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Document Requirements --}}
        @if($requirements && $requirements->isNotEmpty())
        <div class="card-custom mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-list-check me-2 text-primary"></i>Required Documents Checklist
            </div>
            <div class="p-3">
                @foreach($requirements as $req)
                @php $uploadStatus = $req->workerDocument?->review_status ?? 'not_uploaded'; @endphp
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        @if($uploadStatus === 'approved')
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        @elseif($uploadStatus === 'rejected')
                            <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                        @elseif($uploadStatus === 'pending')
                            <i class="bi bi-hourglass-split text-warning fs-5"></i>
                        @else
                            <i class="bi bi-circle text-muted fs-5"></i>
                        @endif
                        <span class="small fw-semibold">{{ $req->documentType?->document_type_name ?? 'Unknown' }}</span>
                    </div>
                    <span class="badge badge-role badge-status-{{ $uploadStatus === 'not_uploaded' ? 'unverified' : $uploadStatus }}">
                        {{ ucfirst(str_replace('_', ' ', $uploadStatus)) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Verification Log --}}
        @if(isset($logs) && $logs->isNotEmpty())
        <div class="card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-clock-history me-2 text-primary"></i>Verification Activity Log
            </div>
            <div class="p-0">
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
                        <div class="small text-muted">
                            <span class="badge bg-light text-dark border">{{ ucfirst($log->entity_type ?? '') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Image Lightbox Modal ────────────────────────────────────────── --}}
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

{{-- ── Reject Note Modal ────────────────────────────────────────────── --}}
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
                          placeholder="e.g. Selfie tidak jelas / Dokumen kadaluarsa / Foto blur..."></textarea>
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

{{-- ── Suspend Confirm Modal ──────────────────────────────────────── --}}
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-semibold text-danger">
                    <i class="bi bi-slash-circle me-2"></i>Suspend User
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">User will be logged out from all devices and their account will be suspended.</p>
                <label class="form-label">Reason (optional)</label>
                <textarea id="suspendNote" class="form-control" rows="2"
                          placeholder="Reason for suspension..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="suspendConfirmBtn">
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

    // ── Selfie Actions ─────────────────────────────────────────────────
    async function approveSelfie(workerId) {
        if (!confirm('Approve selfie for this worker?')) return;
        const res = await apiCall(`/admin/workers/${workerId}/approve-selfie`, 'PUT');
        if (res.success) { showToast(res.message); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    }

    let _rejectTarget = null;
    let _rejectType = null; // 'selfie' | 'document' | 'suspend'
    let _rejectDocId = null;

    function rejectSelfie(workerId) {
        _rejectTarget = workerId;
        _rejectType = 'selfie';
        document.getElementById('rejectModalTitle').textContent = 'Reject Selfie';
        document.getElementById('rejectNote').value = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    // ── Document Actions ───────────────────────────────────────────────
    async function approveDocument(workerId, docId) {
        if (!confirm('Approve this document?')) return;
        const res = await apiCall(`/admin/workers/${workerId}/documents/${docId}/approve`, 'PUT', { note: '' });
        if (res.success) {
            showToast('Document approved.');
            const badge = document.getElementById(`doc-status-badge-${docId}`);
            if (badge) badge.outerHTML = `<span class="badge badge-role badge-status-approved fs-6" id="doc-status-badge-${docId}"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
            setTimeout(() => location.reload(), 1200);
        } else showToast(res.message ?? 'Error', 'danger');
    }

    function rejectDocument(workerId, docId) {
        _rejectTarget = workerId;
        _rejectType = 'document';
        _rejectDocId = docId;
        document.getElementById('rejectModalTitle').textContent = 'Reject Document';
        document.getElementById('rejectNote').value = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    async function approveAllDocsAndSelfie(workerId) {
        if (!confirm('Approve all pending documents and selfie for this worker?')) return;
        
        // 1. Approve Selfie if it's pending (we check if the button exists or just fire the API if there's no verified_at)
        const selfieBtn = document.querySelector('button[onclick^="approveSelfie"]');
        if (selfieBtn) {
            await apiCall(`/admin/workers/${workerId}/approve-selfie`, 'PUT');
            selfieBtn.style.display = 'none';
        }

        // 2. Approve all pending documents
        const pendingBtns = document.querySelectorAll('button[onclick^="approveDocument"]');
        for (const btn of pendingBtns) {
            const match = btn.getAttribute('onclick').match(/approveDocument\(\d+,\s*(\d+)\)/);
            if (match) {
                const docId = match[1];
                await apiCall(`/admin/workers/${workerId}/documents/${docId}/approve`, 'PUT', { note: '' });
                btn.style.display = 'none';
            }
        }
        
        showToast('All pending items approved.');
        setTimeout(() => location.reload(), 1200);
    }


    document.getElementById('rejectConfirmBtn').addEventListener('click', async () => {
        const note = document.getElementById('rejectNote').value.trim();
        if (!note) { alert('Please provide a rejection reason.'); return; }

        let url, body;
        if (_rejectType === 'selfie') {
            url = `/admin/workers/${_rejectTarget}/reject-selfie`;
            body = { note };
        } else {
            url = `/admin/workers/${_rejectTarget}/documents/${_rejectDocId}/reject`;
            body = { note };
        }

        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        const res = await apiCall(url, 'PUT', body);
        if (res.success) { showToast(res.message ?? 'Rejected.'); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    });

    // ── Badge Override ─────────────────────────────────────────────────
    async function submitBadgeOverride() {
        const form = document.getElementById('form-badge-override');
        const workerId = form.dataset.workerId;
        const data = {
            verified_badge_status: form.querySelector('[name=verified_badge_status]').value || undefined,
            ready_to_work_status:  form.querySelector('[name=ready_to_work_status]').value || undefined,
            note: form.querySelector('[name=note]').value,
        };
        const res = await apiCall(`/admin/workers/${workerId}/override-badge`, 'PUT', data);
        if (res.success) { showToast('Badge override applied.'); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    }

    // ── Suspend / Restore User ─────────────────────────────────────────
    function confirmSuspendUser(userId) {
        _rejectTarget = userId;
        document.getElementById('suspendNote').value = '';
        new bootstrap.Modal(document.getElementById('suspendModal')).show();
    }

    document.getElementById('suspendConfirmBtn').addEventListener('click', async () => {
        const note = document.getElementById('suspendNote').value.trim();
        bootstrap.Modal.getInstance(document.getElementById('suspendModal')).hide();
        const res = await apiCall(`/admin/workers/suspend-user/${_rejectTarget}`, 'PUT', { note });
        if (res.success) { showToast(res.message); setTimeout(() => location.reload(), 1500); }
        else showToast(res.message ?? 'Error', 'danger');
    });

    async function restoreUser(userId) {
        if (!confirm('Restore this user?')) return;
        const res = await apiCall(`/admin/workers/restore-user/${userId}`, 'PUT', {});
        if (res.success) { showToast(res.message); setTimeout(() => location.reload(), 1200); }
        else showToast(res.message ?? 'Error', 'danger');
    }
</script>
@endsection

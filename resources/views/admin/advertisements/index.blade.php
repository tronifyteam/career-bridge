@extends('admin.layouts.app')

@section('title', 'Advertisements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Advertisement Management</h4>
        <p class="text-muted mb-0">Review and monitor Banner Ads and Sponsored Jobs.</p>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.advertisements.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="banner" {{ request('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="sponsored_job" {{ request('type') == 'sponsored_job' ? 'selected' : '' }}>Sponsored Job</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.advertisements.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ad Info</th>
                    <th>Employer</th>
                    <th>Package</th>
                    <th>Period</th>
                    <th>Performance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($advertisements as $ad)
                <tr>
                    <td>
                        @if($ad->type === 'banner')
                            <div class="fw-bold"><i class="bi bi-image me-1 text-primary"></i> {{ $ad->title ?? 'Banner Ad' }}</div>
                            @if($ad->image_url)
                                <a href="{{ $ad->image_url }}" target="_blank" class="small text-muted text-decoration-none">View Image</a>
                            @endif
                        @else
                            <div class="fw-bold"><i class="bi bi-briefcase-fill me-1 text-warning"></i> Sponsored Job</div>
                            <div class="small">
                                <a href="{{ route('admin.jobs.show', $ad->job_id) }}" class="text-decoration-none">Job #{{ $ad->job_id }}</a>
                            </div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $ad->user_id) }}" class="text-primary fw-semibold text-decoration-none">
                            {{ $ad->user->name ?? 'Unknown' }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $ad->package->name ?? 'Custom' }}</div>
                        <div class="small text-muted">{{ $ad->package ? $ad->package->duration_days . ' Days' : '' }}</div>
                    </td>
                    <td class="small text-muted">
                        {{ $ad->starts_at ? $ad->starts_at->format('M d, Y') : '-' }} <br>
                        to {{ $ad->expires_at ? $ad->expires_at->format('M d, Y') : '-' }}
                    </td>
                    <td>
                        <div class="small">Views: <span class="fw-bold">{{ number_format($ad->impressions_count) }}</span></div>
                        <div class="small">Clicks: <span class="fw-bold">{{ number_format($ad->clicks_count) }}</span></div>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($ad->status) {
                                'active' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'paused' => 'bg-secondary',
                                'rejected' => 'bg-danger',
                                'expired' => 'bg-dark',
                                default => 'bg-light text-dark'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ strtoupper($ad->status) }}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#actionModal{{ $ad->id }}">
                            Manage
                        </button>

                        <!-- Action Modal -->
                        <div class="modal fade" id="actionModal{{ $ad->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.advertisements.update_status', $ad->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Manage Advertisement #{{ $ad->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Update Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" {{ $ad->status == 'active' ? 'selected' : '' }}>Active (Approve/Resume)</option>
                                                    <option value="paused" {{ $ad->status == 'paused' ? 'selected' : '' }}>Paused (Temporary stop)</option>
                                                    <option value="rejected" {{ $ad->status == 'rejected' ? 'selected' : '' }}>Rejected (Not allowed)</option>
                                                    <option value="expired" {{ $ad->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admin Note (Optional)</label>
                                                <textarea name="admin_note" class="form-control" rows="2" placeholder="Reason for rejection, etc">{{ $ad->admin_note }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No advertisements found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($advertisements->hasPages())
    <div class="p-3 border-top">
        {{ $advertisements->links() }}
    </div>
    @endif
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Agency Verification')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-diagram-3 me-2 text-success"></i>Agency Verification</h4>
        <p class="text-muted mb-0">Manage agency accounts and their staff</p>
    </div>
</div>

{{-- Stats Bar --}}
<div class="row g-3 mb-4">
    @php
        $byType = [
            'agency'       => ['icon' => 'bi-diagram-3',       'color' => 'primary',   'label' => 'Main Agency'],
            'agency_staff' => ['icon' => 'bi-person-badge',    'color' => 'secondary', 'label' => 'Agency Staff'],
        ];
    @endphp
    @foreach($byType as $type => $meta)
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-{{ $meta['color'] }} bg-opacity-15 text-{{ $meta['color'] }}">
                <i class="bi {{ $meta['icon'] }}"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.5rem;">
                    {{ $agencies->filter(fn($e) => $e->role === $type)->count() }}
                </div>
                <div class="stat-label">{{ $meta['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.agencies.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Agency name, email, number..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="agency"       {{ request('role') == 'agency'       ? 'selected' : '' }}>Main Agency</option>
                    <option value="agency_staff" {{ request('role') == 'agency_staff' ? 'selected' : '' }}>Agency Staff</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Verification Status</label>
                <select name="verification_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="unverified"        {{ request('verification_status') == 'unverified'        ? 'selected' : '' }}>Unverified</option>
                    <option value="pending"           {{ request('verification_status') == 'pending'           ? 'selected' : '' }}>Pending</option>
                    <option value="basic_verified"    {{ request('verification_status') == 'basic_verified'    ? 'selected' : '' }}>Basic Verified</option>
                    <option value="manually_verified" {{ request('verification_status') == 'manually_verified' ? 'selected' : '' }}>Manually Verified</option>
                    <option value="rejected"          {{ request('verification_status') == 'rejected'          ? 'selected' : '' }}>Rejected</option>
                    <option value="suspended"         {{ request('verification_status') == 'suspended'         ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Badge</label>
                <select name="badge_status" class="form-select">
                    <option value="">All Badges</option>
                    <option value="unverified" {{ request('badge_status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                    <option value="pending"    {{ request('badge_status') == 'pending'    ? 'selected' : '' }}>Pending</option>
                    <option value="verified"   {{ request('badge_status') == 'verified'   ? 'selected' : '' }}>Verified</option>
                    <option value="rejected"   {{ request('badge_status') == 'rejected'   ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.agencies.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Agencies Table --}}
<div class="card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Agencies & Staff List</span>
        <small class="text-muted">{{ $agencies->total() }} total · Page {{ $agencies->currentPage() }} of {{ $agencies->lastPage() }}</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Account</th>
                    <th>Role</th>
                    <th>Company / Info</th>
                    <th>Verification</th>
                    <th>Verified Badge</th>
                    <th>Documents</th>
                    <th>Jobs Posted</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agencies as $agency)
                <tr>
                    <td class="text-muted small">{{ $agency->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $agency->full_name ?? $agency->name }}</div>
                        <small class="text-muted">{{ $agency->email }}</small>
                    </td>
                    <td>
                        @php
                            $roleColors = [
                                'agency'      => 'info',
                                'agency_staff'=> 'secondary',
                            ];
                            $roleColor = $roleColors[$agency->role] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} border border-{{ $roleColor }}-subtle">
                            {{ str_replace('_', ' ', ucfirst($agency->role ?? '—')) }}
                        </span>
                    </td>
                    <td class="small">
                        <div>{{ $agency->company_name ?? '—' }}</div>
                        @if($agency->unified_business_number)
                        <small class="text-muted">UBN: {{ $agency->unified_business_number }}</small>
                        @endif
                        @if($agency->license_number)
                        <small class="text-muted">Lic: {{ $agency->license_number }}</small>
                        @endif
                    </td>
                    <td>
                        @php $vs = $agency->verification_status ?? 'unverified'; @endphp
                        <span class="badge badge-role badge-status-{{ $vs }}">
                            {{ str_replace('_', ' ', ucfirst($vs)) }}
                        </span>
                    </td>
                    <td>
                        @php $badge = $agency->verified_badge_status ?? 'unverified'; @endphp
                        <span class="badge badge-role badge-status-{{ $badge }}">
                            @if($badge === 'verified') <i class="bi bi-patch-check-fill me-1"></i>
                            @elseif($badge === 'pending') <i class="bi bi-hourglass-split me-1"></i>
                            @elseif($badge === 'rejected') <i class="bi bi-x-circle me-1"></i>
                            @endif
                            {{ ucfirst($badge) }}
                        </span>
                    </td>
                    <td>
                        @php $docCount = $agency->documents->count(); @endphp
                        @if($docCount > 0)
                            @php $pendingDocs = $agency->documents->filter(fn($d) => ($d->status ?? 'pending') === 'pending')->count(); @endphp
                            <span class="badge {{ $pendingDocs > 0 ? 'bg-warning text-dark' : 'bg-success-subtle text-success border border-success-subtle' }}">
                                <i class="bi bi-file-earmark me-1"></i>{{ $docCount }} doc{{ $docCount > 1 ? 's' : '' }}
                                @if($pendingDocs > 0)
                                    · {{ $pendingDocs }} pending
                                @endif
                            </span>
                        @else
                            <span class="text-muted small">No docs</span>
                        @endif
                    </td>
                    <td class="small text-center">
                        @php $jobCount = $agency->jobs_count ?? 0; @endphp
                        @if($jobCount > 0)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $jobCount }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $agency->created_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.agencies.show', $agency) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-shield-check me-1"></i>Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="bi bi-diagram-3 display-6 d-block mb-2 opacity-25"></i>
                        No agencies found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agencies->hasPages())
    <div class="p-3 border-top">
        {{ $agencies->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

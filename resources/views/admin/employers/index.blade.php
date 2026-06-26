@extends('admin.layouts.app')

@section('title', 'Employer Verification')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-building-check me-2 text-success"></i>Employer Verification</h4>
        <p class="text-muted mb-0">Manage employer accounts — Company, Factory, Family Care & Agency</p>
    </div>
</div>

{{-- Stats Bar --}}
<div class="row g-3 mb-4">
    @php
        $byType = [
            'company'     => ['icon' => 'bi-building',        'color' => 'primary',   'label' => 'Company'],
            'factory'     => ['icon' => 'bi-gear-fill',        'color' => 'warning',   'label' => 'Factory'],
            'family_care' => ['icon' => 'bi-house-heart-fill', 'color' => 'danger',    'label' => 'Family Care'],
            'agency'      => ['icon' => 'bi-people-fill',      'color' => 'info',      'label' => 'Agency'],
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
                    {{ $employers->filter(fn($e) => $e->role === $type)->count() }}
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
        <form method="GET" action="{{ route('admin.employers.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Company name, email, number..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Employer Type</label>
                <select name="role" class="form-select">
                    <option value="">All Types</option>
                    <option value="company"     {{ request('role') == 'company'     ? 'selected' : '' }}>Company</option>
                    <option value="factory"     {{ request('role') == 'factory'     ? 'selected' : '' }}>Factory</option>
                    <option value="family_care" {{ request('role') == 'family_care' ? 'selected' : '' }}>Family Care</option>
                    <option value="agency"      {{ request('role') == 'agency'      ? 'selected' : '' }}>Agency</option>
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
                <a href="{{ route('admin.employers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Employers Table --}}
<div class="card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Employers List</span>
        <small class="text-muted">{{ $employers->total() }} total · Page {{ $employers->currentPage() }} of {{ $employers->lastPage() }}</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employer</th>
                    <th>Type</th>
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
                @forelse($employers as $employer)
                <tr>
                    <td class="text-muted small">{{ $employer->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $employer->full_name ?? $employer->name }}</div>
                        <small class="text-muted">{{ $employer->email }}</small>
                    </td>
                    <td>
                        @php
                            $roleColors = [
                                'company'     => 'primary',
                                'factory'     => 'warning',
                                'family_care' => 'danger',
                                'agency'      => 'info',
                                'agency_staff'=> 'secondary',
                            ];
                            $roleColor = $roleColors[$employer->role] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} border border-{{ $roleColor }}-subtle">
                            {{ str_replace('_', ' ', ucfirst($employer->role ?? '—')) }}
                        </span>
                    </td>
                    <td class="small">
                        <div>{{ $employer->company_name ?? '—' }}</div>
                        @if($employer->unified_business_number)
                        <small class="text-muted">UBN: {{ $employer->unified_business_number }}</small>
                        @endif
                        @if($employer->license_number)
                        <small class="text-muted">Lic: {{ $employer->license_number }}</small>
                        @endif
                    </td>
                    <td>
                        @php $vs = $employer->verification_status ?? 'unverified'; @endphp
                        <span class="badge badge-role badge-status-{{ $vs }}">
                            {{ str_replace('_', ' ', ucfirst($vs)) }}
                        </span>
                    </td>
                    <td>
                        @php $badge = $employer->verified_badge_status ?? 'unverified'; @endphp
                        <span class="badge badge-role badge-status-{{ $badge }}">
                            @if($badge === 'verified') <i class="bi bi-patch-check-fill me-1"></i>
                            @elseif($badge === 'pending') <i class="bi bi-hourglass-split me-1"></i>
                            @elseif($badge === 'rejected') <i class="bi bi-x-circle me-1"></i>
                            @endif
                            {{ ucfirst($badge) }}
                        </span>
                    </td>
                    <td>
                        @php $docCount = $employer->documents->count(); @endphp
                        @if($docCount > 0)
                            @php $pendingDocs = $employer->documents->filter(fn($d) => ($d->status ?? 'pending') === 'pending')->count(); @endphp
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
                        @php $jobCount = $employer->jobs_count ?? 0; @endphp
                        @if($jobCount > 0)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $jobCount }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $employer->created_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.employers.show', $employer) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-shield-check me-1"></i>Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="bi bi-building display-6 d-block mb-2 opacity-25"></i>
                        No employers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employers->hasPages())
    <div class="p-3 border-top">
        {{ $employers->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

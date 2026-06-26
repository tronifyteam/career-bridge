@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Users</h4>
        <p class="text-muted mb-0">Manage platform users</p>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, company..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="worker" {{ request('role') == 'worker' ? 'selected' : '' }}>Worker</option>
                    <option value="company" {{ request('role') == 'company' ? 'selected' : '' }}>Company</option>
                    <option value="factory" {{ request('role') == 'factory' ? 'selected' : '' }}>Factory</option>
                    <option value="family_care" {{ request('role') == 'family_care' ? 'selected' : '' }}>Family Care</option>
                    <option value="agency" {{ request('role') == 'agency' ? 'selected' : '' }}>Agency</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Verification</label>
                <select name="verification_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="unverified" {{ request('verification_status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                    <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="basic_verified" {{ request('verification_status') == 'basic_verified' ? 'selected' : '' }}>Basic Verified</option>
                    <option value="manually_verified" {{ request('verification_status') == 'manually_verified' ? 'selected' : '' }}>Manually Verified</option>
                    <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Company/Nationality</th>
                    <th>Profile</th>
                    <th>Verification</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted">{{ $user->id }}</td>
                    <td class="fw-semibold">{{ $user->full_name ?? $user->name }}</td>
                    <td class="small">{{ $user->email }}</td>
                    <td>
                        @if($user->role)
                            <span class="badge badge-role badge-{{ $user->role }}">{{ str_replace('_', ' ', ucfirst($user->role)) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small">
                        @if($user->isEmployer())
                            {{ $user->company_name ?? '—' }}
                        @else
                            {{ $user->nationality ?? '—' }}
                        @endif
                    </td>
                    <td>
                        @if($user->profile_completed)
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                        @else
                            <span class="text-warning"><i class="bi bi-exclamation-circle"></i></span>
                        @endif
                    </td>
                    <td>
                        @if($user->isEmployer())
                            <span class="badge badge-role badge-status-{{ $user->verification_status ?? 'unverified' }}">
                                {{ str_replace('_', ' ', ucfirst($user->verification_status ?? 'unverified')) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $user->created_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-3 border-top">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

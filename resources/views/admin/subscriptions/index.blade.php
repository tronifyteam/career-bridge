@extends('admin.layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Active Subscriptions</h4>
        <p class="text-muted mb-0">Monitor user subscription plans and translation quotas.</p>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search User</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                    <th>User</th>
                    <th>Plan Type</th>
                    <th>Translation Quota</th>
                    <th>Starts At</th>
                    <th>Expires At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                <tr>
                    <td class="text-muted">#{{ $sub->id }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $sub->user_id) }}" class="text-primary fw-semibold text-decoration-none">
                            {{ $sub->user->name ?? 'Unknown' }}
                        </a>
                        <div class="small text-muted">{{ $sub->user->email ?? '' }}</div>
                    </td>
                    <td><span class="badge bg-primary">{{ ucfirst($sub->plan_type) }}</span></td>
                    <td class="fw-bold">{{ number_format($sub->chat_translation_quota) }} chars</td>
                    <td class="small">{{ $sub->starts_at?->format('M d, Y') ?? 'N/A' }}</td>
                    <td class="small {{ $sub->expires_at && $sub->expires_at->isPast() ? 'text-danger fw-bold' : '' }}">
                        {{ $sub->expires_at?->format('M d, Y') ?? 'Lifetime' }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($sub->status) {
                                'active' => 'bg-success',
                                'expired' => 'bg-danger',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-light text-dark'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($sub->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No subscriptions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
    <div class="p-3 border-top">
        {{ $subscriptions->links() }}
    </div>
    @endif
</div>
@endsection

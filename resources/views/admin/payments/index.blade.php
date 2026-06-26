@extends('admin.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Payments History</h4>
        <p class="text-muted mb-0">Monitor all incoming payments from users.</p>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filters
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search User or Transaction ID</label>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="small">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $payment->user_id) }}" class="text-primary fw-semibold text-decoration-none">
                            {{ $payment->user->name ?? 'Unknown' }}
                        </a>
                        <div class="small text-muted">{{ $payment->user->email ?? '' }}</div>
                    </td>
                    <td class="fw-bold text-success">${{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $payment->payment_gateway }}</span></td>
                    <td class="font-monospace small text-muted">{{ $payment->transaction_id }}</td>
                    <td>
                        @php
                            $badgeClass = match($payment->status) {
                                'success' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'failed' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No payments found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="p-3 border-top">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection

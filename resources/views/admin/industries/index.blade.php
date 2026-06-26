@extends('admin.layouts.app')

@section('title', 'Manage Industries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">Industries</h2>
    <a href="{{ route('admin.industries.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Industry
    </a>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.industries.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.industries.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        Industries List
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($industries as $industry)
                <tr>
                    <td class="text-muted">#{{ $industry->id }}</td>
                    <td class="fw-medium">{{ $industry->name }}</td>
                    <td><code>{{ $industry->slug }}</code></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.industries.edit', $industry) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.industries.destroy', $industry) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this industry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">No industries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($industries->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $industries->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

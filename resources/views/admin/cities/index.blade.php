@extends('admin.layouts.app')

@section('title', 'Manage Cities')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">Cities</h2>
    <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add City
    </a>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.cities.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        Cities List
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Region</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cities as $city)
                <tr>
                    <td class="text-muted">#{{ $city->id }}</td>
                    <td class="fw-medium">{{ $city->name }}</td>
                    <td>{{ $city->region ?? '-' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this city?');">
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
                    <td colspan="4" class="text-center py-4 text-muted">No cities found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cities->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $cities->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Manage Skills')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">Skills</h2>
    <a href="{{ route('admin.skills.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Skill
    </a>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.skills.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.skills.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        Skills List
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
                @forelse($skills as $skill)
                <tr>
                    <td class="text-muted">#{{ $skill->id }}</td>
                    <td class="fw-medium">{{ $skill->name }}</td>
                    <td><code>{{ $skill->slug }}</code></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.skills.edit', $skill) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.skills.destroy', $skill) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this skill?');">
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
                    <td colspan="4" class="text-center py-4 text-muted">No skills found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($skills->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $skills->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

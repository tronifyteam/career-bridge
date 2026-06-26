@extends('admin.layouts.app')

@section('title', 'Manage Languages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">Languages</h2>
    <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Language
    </a>
</div>

{{-- Filters --}}
<div class="card-custom mb-4">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filters</div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.languages.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search Language</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.languages.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        Languages List
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($languages as $lang)
                <tr>
                    <td class="text-muted">#{{ $lang->id }}</td>
                    <td class="fw-bold"><span class="badge bg-secondary">{{ $lang->language_code }}</span></td>
                    <td class="fw-medium">{{ $lang->language_name }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.languages.edit', $lang) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.languages.destroy', $lang) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this language?');">
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
                    <td colspan="4" class="text-center py-4 text-muted">No languages found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($languages->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $languages->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

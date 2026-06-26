@extends('admin.layouts.app')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $category->exists ? 'Edit Category' : 'Add New Category' }}</h2>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        Category Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label fw-medium">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required placeholder="e.g. Construction">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">The slug will be automatically generated from the name.</div>
            </div>

            <div class="mb-3">
                <label for="icon" class="form-label fw-medium">Icon Upload</label>
                
                @if($category->exists && $category->icon)
                    <div class="mb-2">
                        <p class="small text-muted mb-1">Current Icon:</p>
                        <img src="{{ filter_var($category->icon, FILTER_VALIDATE_URL) ? $category->icon : asset('storage/' . $category->icon) }}" alt="Current icon" style="width: 48px; height: 48px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; padding: 2px;">
                    </div>
                @endif
                
                <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" accept="image/*">
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Upload a square image (PNG/JPG). Max size: 2MB.</div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-medium">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Brief description of this category">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $category->exists ? 'Update Category' : 'Save Category' }}
            </button>
        </form>
    </div>
</div>
@endsection

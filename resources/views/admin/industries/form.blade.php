@extends('admin.layouts.app')

@section('title', $industry->exists ? 'Edit Industry' : 'Add Industry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $industry->exists ? 'Edit Industry' : 'Add New Industry' }}</h2>
    <a href="{{ route('admin.industries.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        Industry Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $industry->exists ? route('admin.industries.update', $industry) : route('admin.industries.store') }}" method="POST">
            @csrf
            @if($industry->exists)
                @method('PUT')
            @endif

            <div class="mb-4">
                <label for="name" class="form-label fw-medium">Industry Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $industry->name) }}" required placeholder="e.g. Manufacturing, Healthcare">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">The slug will be automatically generated from the name.</div>
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $industry->exists ? 'Update Industry' : 'Save Industry' }}
            </button>
        </form>
    </div>
</div>
@endsection

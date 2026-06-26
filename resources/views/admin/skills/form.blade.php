@extends('admin.layouts.app')

@section('title', $skill->exists ? 'Edit Skill' : 'Add Skill')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $skill->exists ? 'Edit Skill' : 'Add New Skill' }}</h2>
    <a href="{{ route('admin.skills.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        Skill Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $skill->exists ? route('admin.skills.update', $skill) : route('admin.skills.store') }}" method="POST">
            @csrf
            @if($skill->exists)
                @method('PUT')
            @endif

            <div class="mb-4">
                <label for="name" class="form-label fw-medium">Skill Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $skill->name) }}" required placeholder="e.g. Welding, Driving">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">The slug will be automatically generated from the name.</div>
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $skill->exists ? 'Update Skill' : 'Save Skill' }}
            </button>
        </form>
    </div>
</div>
@endsection

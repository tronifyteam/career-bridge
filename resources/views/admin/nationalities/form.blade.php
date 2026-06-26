@extends('admin.layouts.app')

@section('title', $nationality->exists ? 'Edit Nationality' : 'Add Nationality')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $nationality->exists ? 'Edit Nationality' : 'Add New Nationality' }}</h2>
    <a href="{{ route('admin.nationalities.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        Nationality Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $nationality->exists ? route('admin.nationalities.update', $nationality) : route('admin.nationalities.store') }}" method="POST">
            @csrf
            @if($nationality->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label fw-medium">Nationality Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $nationality->name) }}" required placeholder="e.g. Indonesia, Philippines, Vietnam">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Display name of the country/nationality. Must be unique.</div>
            </div>

            <div class="mb-4">
                <label for="code" class="form-label fw-medium">Country Code</label>
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $nationality->code) }}" placeholder="e.g. ID, PH, VN" style="max-width: 200px;">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">A unique ISO or short code for the nationality (optional).</div>
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $nationality->exists ? 'Update Nationality' : 'Save Nationality' }}
            </button>
        </form>
    </div>
</div>
@endsection

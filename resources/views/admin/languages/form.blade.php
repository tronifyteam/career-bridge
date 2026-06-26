@extends('admin.layouts.app')

@section('title', $language->exists ? 'Edit Language' : 'Add Language')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $language->exists ? 'Edit Language' : 'Add New Language' }}</h2>
    <a href="{{ route('admin.languages.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        Language Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $language->exists ? route('admin.languages.update', $language) : route('admin.languages.store') }}" method="POST">
            @csrf
            @if($language->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="language_code" class="form-label fw-medium">Language Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('language_code') is-invalid @enderror" id="language_code" name="language_code" value="{{ old('language_code', $language->language_code) }}" required placeholder="e.g. EN, ID, VI" style="max-width: 200px;">
                @error('language_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">A unique code used to identify the language (usually 2 characters, capitalized).</div>
            </div>

            <div class="mb-4">
                <label for="language_name" class="form-label fw-medium">Language Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('language_name') is-invalid @enderror" id="language_name" name="language_name" value="{{ old('language_name', $language->language_name) }}" required placeholder="e.g. English, Bahasa Indonesia">
                @error('language_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Display name of the language.</div>
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $language->exists ? 'Update Language' : 'Save Language' }}
            </button>
        </form>
    </div>
</div>
@endsection

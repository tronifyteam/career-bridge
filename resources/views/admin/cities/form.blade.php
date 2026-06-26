@extends('admin.layouts.app')

@section('title', $city->exists ? 'Edit City' : 'Add City')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">{{ $city->exists ? 'Edit City' : 'Add New City' }}</h2>
    <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        City Details
    </div>
    <div class="card-body p-4">
        <form action="{{ $city->exists ? route('admin.cities.update', $city) : route('admin.cities.store') }}" method="POST">
            @csrf
            @if($city->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label fw-medium">City Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $city->name) }}" required placeholder="e.g. Taipei">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="region" class="form-label fw-medium">Region</label>
                <input type="text" class="form-control @error('region') is-invalid @enderror" id="region" name="region" value="{{ old('region', $city->region) }}" placeholder="e.g. Northern Taiwan">
                @error('region')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="mb-4">
            
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> {{ $city->exists ? 'Update City' : 'Save City' }}
            </button>
        </form>
    </div>
</div>
@endsection

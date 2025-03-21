@extends('layouts.app')

@section('title', 'Create Tax Rate')
@section('page-title', 'Create New Tax Rate')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}">Tax Rates</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">New Tax Rate</h5>
                <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Tax Rates
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-rates.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tax Rate Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" required autofocus>
                        <div class="form-text">Example: Standard Rate, Reduced Rate, Zero Rate, etc.</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="percentage" class="form-label">Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" 
                            class="form-control @error('percentage') is-invalid @enderror" 
                            id="percentage" name="percentage" value="{{ old('percentage', 0) }}" required>
                        <div class="form-text">Enter a value between 0 and 100. Use 0 for zero-rated or exempt items.</div>
                        @error('percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input @error('is_default') is-invalid @enderror" 
                            type="checkbox" id="is_default" name="is_default" value="1" 
                            {{ old('is_default') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">
                            Set as Default Tax Rate
                        </label>
                        <div class="form-text">The default tax rate will be automatically selected for new invoices and services.</div>
                        @error('is_default')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Create Tax Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Help</h5>
            </div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">About Tax Rates</h6>
                <p>Tax rates are used to calculate taxes on invoices and services. Each business can have multiple tax rates, but must have at least one default tax rate.</p>
                
                <h6 class="mt-3">Examples</h6>
                <ul class="list-unstyled">
                    <li><strong>Standard Rate:</strong> The full tax rate, e.g., 20%</li>
                    <li><strong>Reduced Rate:</strong> Lower rate for certain goods and services, e.g., 5%</li>
                    <li><strong>Zero Rate:</strong> 0% rate for exempt items</li>
                </ul>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i> Setting a tax rate as default will automatically replace the current default tax rate.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

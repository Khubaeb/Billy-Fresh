@extends('layouts.app')

@section('title', 'Edit Tax Rate')
@section('page-title', 'Edit Tax Rate')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}">Tax Rates</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit {{ $taxRate->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Tax Rate</h5>
                <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Tax Rates
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-rates.update', $taxRate) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tax Rate Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name', $taxRate->name) }}" required autofocus>
                        <div class="form-text">Example: Standard Rate, Reduced Rate, Zero Rate, etc.</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="percentage" class="form-label">Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" 
                            class="form-control @error('percentage') is-invalid @enderror" 
                            id="percentage" name="percentage" value="{{ old('percentage', $taxRate->percentage) }}" required>
                        <div class="form-text">Enter a value between 0 and 100. Use 0 for zero-rated or exempt items.</div>
                        @error('percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input @error('is_default') is-invalid @enderror" 
                            type="checkbox" id="is_default" name="is_default" value="1" 
                            {{ old('is_default', $taxRate->is_default) ? 'checked' : '' }} 
                            {{ $taxRate->is_default ? 'disabled' : '' }}>
                        <label class="form-check-label" for="is_default">
                            Set as Default Tax Rate
                        </label>
                        @if($taxRate->is_default)
                            <div class="form-text text-success">
                                <i class="bi bi-check-circle me-1"></i> This is already set as the default tax rate.
                                <input type="hidden" name="is_default" value="1">
                            </div>
                        @else
                            <div class="form-text">The default tax rate will be automatically selected for new invoices and services.</div>
                        @endif
                        @error('is_default')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Update Tax Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Help</h5>
            </div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">About Tax Rates</h6>
                <p>Tax rates are used to calculate taxes on invoices and services. Each business can have multiple tax rates, but must have at least one default tax rate.</p>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i> If you need to change the default tax rate, you can either mark this tax rate as default, or go to the tax rates list and set a different tax rate as default.
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Danger Zone</h5>
            </div>
            <div class="card-body">
                <p>Delete this tax rate permanently.</p>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTaxRateModal">
                    <i class="bi bi-trash me-1"></i> Delete Tax Rate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteTaxRateModal" tabindex="-1" aria-labelledby="deleteTaxRateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaxRateModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the tax rate <strong>{{ $taxRate->name }} ({{ $taxRate->formatted_percentage }})</strong>?</p>
                <p class="text-danger">Warning: This action cannot be undone.</p>
                
                @if($taxRate->is_default)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> You cannot delete the default tax rate. Please set another tax rate as default first.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('tax-rates.destroy', $taxRate) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" {{ $taxRate->is_default ? 'disabled' : '' }}>
                        Delete Tax Rate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

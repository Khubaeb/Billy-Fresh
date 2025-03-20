@extends('layouts.app')

@section('title', 'Create Service')
@section('page-title', 'Create Service')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Services</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Service</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('services.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Detailed description of the service or product</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label">SKU/Code</label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique identifier for your service or product</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit') }}">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">e.g., hour, piece, license, month</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', '0.00') }}" min="0" step="0.01" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cost" class="form-label">Cost Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost') }}" min="0" step="0.01">
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Your cost (optional, helps calculate profit)</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="tax_rate" class="form-label">Tax Rate</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" value="{{ old('tax_rate') }}" min="0" max="100" step="0.01">
                                <span class="input-group-text">%</span>
                                @error('tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Default tax rate for this service</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Service is active</label>
                        </div>
                        <div class="form-text">Inactive services won't appear in dropdown menus when creating invoices</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Create Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="mb-0">Service Tips</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">Use clear, descriptive service names that your customers will understand.</li>
                    <li class="mb-2">Adding a cost price helps you track profitability for each service.</li>
                    <li class="mb-2">SKU codes are optional but help with organization and inventory management.</li>
                    <li class="mb-2">Set a default tax rate to save time when adding this service to invoices.</li>
                    <li class="mb-2">Inactive services can still be viewed in reports but won't appear during invoice creation.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById('price');
        const costInput = document.getElementById('cost');
        const profitMarginElement = document.getElementById('profit-margin');
        
        // Calculate and display profit margin when inputs change
        function updateProfitMargin() {
            const price = parseFloat(priceInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            
            if (price > 0 && cost > 0) {
                const margin = ((price - cost) / price) * 100;
                profitMarginElement.textContent = margin.toFixed(1) + '%';
            } else {
                profitMarginElement.textContent = 'N/A';
            }
        }
        
        if (priceInput && costInput && profitMarginElement) {
            priceInput.addEventListener('input', updateProfitMargin);
            costInput.addEventListener('input', updateProfitMargin);
            updateProfitMargin(); // Initial calculation
        }
    });
</script>
@endpush

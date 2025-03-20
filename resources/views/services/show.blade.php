@extends('layouts.app')

@section('title', $service->name)
@section('page-title', 'Service Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Services</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Service Information</h5>
                <div>
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Name</h6>
                        <p class="fs-5 fw-medium">{{ $service->name }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">SKU/Code</h6>
                        <p>{{ $service->sku ?: 'Not specified' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Status</h6>
                        @if($service->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-1">Description</h6>
                        <p>{{ $service->description ?: 'No description provided' }}</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Selling Price</h6>
                        <p class="fs-5 fw-medium">${{ $service->formatted_price }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Cost Price</h6>
                        <p>${{ $service->cost ? number_format($service->cost, 2) : 'Not specified' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Profit</h6>
                        <p>{{ $service->profit ? '$' . number_format($service->profit, 2) : 'Not calculated' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Margin</h6>
                        <p>{{ $service->margin ? number_format($service->margin, 1) . '%' : 'Not calculated' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Tax Rate</h6>
                        <p>{{ $service->tax_rate ? $service->tax_rate . '%' : 'Not specified' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Unit</h6>
                        <p>{{ $service->unit ?: 'Not specified' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Created</h6>
                        <p>{{ $service->created_at->format('m/d/Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Last Updated</h6>
                        <p>{{ $service->updated_at->format('m/d/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Usage</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Invoices Using This Service</h6>
                        <p class="fs-4">{{ $invoiceCount }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Total Revenue Generated</h6>
                        <p class="fs-4">${{ number_format($totalRevenue, 2) }}</p>
                    </div>
                </div>
                
                @if($invoiceCount > 0)
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i> This service is used in invoices and cannot be deleted. You can deactivate it instead if you no longer offer this service.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Sidebar Card: Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-receipt me-1"></i> Create Invoice with This Service
                    </a>
                    @if($service->is_active)
                        <form action="{{ route('services.update', $service) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $service->name }}">
                            <input type="hidden" name="description" value="{{ $service->description }}">
                            <input type="hidden" name="sku" value="{{ $service->sku }}">
                            <input type="hidden" name="price" value="{{ $service->price }}">
                            <input type="hidden" name="cost" value="{{ $service->cost }}">
                            <input type="hidden" name="tax_rate" value="{{ $service->tax_rate }}">
                            <input type="hidden" name="unit" value="{{ $service->unit }}">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-eye-slash me-1"></i> Deactivate Service
                            </button>
                        </form>
                    @else
                        <form action="{{ route('services.update', $service) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $service->name }}">
                            <input type="hidden" name="description" value="{{ $service->description }}">
                            <input type="hidden" name="sku" value="{{ $service->sku }}">
                            <input type="hidden" name="price" value="{{ $service->price }}">
                            <input type="hidden" name="cost" value="{{ $service->cost }}">
                            <input type="hidden" name="tax_rate" value="{{ $service->tax_rate }}">
                            <input type="hidden" name="unit" value="{{ $service->unit }}">
                            <input type="hidden" name="is_active" value="1">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-eye me-1"></i> Activate Service
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Sidebar Card: Similar Services (placeholder for future functionality) -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Tips</h5>
            </div>
            <div class="card-body">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Keep your service catalog up-to-date with the latest pricing.</li>
                    <li class="mb-2">Use the SKU field to track inventory if this is a physical product.</li>
                    <li class="mb-2">Deactivate services instead of deleting them to maintain invoice history.</li>
                    <li class="mb-2">Track your costs to understand profitability for each service.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServiceModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $service->name }}</strong>? This action cannot be undone.
                
                @if($invoiceCount > 0)
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i> This service is used in {{ $invoiceCount }} invoice(s) and cannot be deleted. Consider deactivating it instead.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('services.destroy', $service) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" {{ $invoiceCount > 0 ? 'disabled' : '' }}>
                        Delete Service
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

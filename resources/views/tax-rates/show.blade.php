@extends('layouts.app')

@section('title', $taxRate->name)
@section('page-title', $taxRate->name)

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}">Tax Rates</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $taxRate->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">Tax Rate Details</h5>
                    @if($taxRate->is_default)
                        <span class="badge bg-success ms-2">Default</span>
                    @endif
                </div>
                <div>
                    <a href="{{ route('tax-rates.edit', $taxRate) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-4 bg-light rounded">
                            <span class="d-block display-4 fw-bold text-primary">{{ $taxRate->formatted_percentage }}</span>
                            <span class="text-muted">{{ $taxRate->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th style="width: 150px;">Name:</th>
                                    <td>{{ $taxRate->name }}</td>
                                </tr>
                                <tr>
                                    <th>Percentage:</th>
                                    <td>{{ $taxRate->formatted_percentage }} ({{ $taxRate->percentage }}%)</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($taxRate->is_default)
                                            <span class="badge bg-success">Default Tax Rate</span>
                                        @else
                                            <span class="badge bg-secondary">Standard Tax Rate</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Business:</th>
                                    <td>
                                        <a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $taxRate->created_at->format('F j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $taxRate->updated_at->format('F j, Y g:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Usage in Invoices</h6>
                                <div class="d-flex align-items-center">
                                    <div class="display-4 me-3">{{ $invoiceCount }}</div>
                                    <div>
                                        <span class="d-block text-muted">Invoice{{ $invoiceCount != 1 ? 's' : '' }}</span>
                                        <a href="#" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-receipt me-1"></i> View Invoices
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Usage in Services</h6>
                                <div class="d-flex align-items-center">
                                    <div class="display-4 me-3">{{ $serviceCount }}</div>
                                    <div>
                                        <span class="d-block text-muted">Service{{ $serviceCount != 1 ? 's' : '' }}</span>
                                        <a href="#" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-tools me-1"></i> View Services
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <a href="{{ route('tax-rates.edit', $taxRate) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i> Edit Tax Rate
                            </a>
                            @if(!$taxRate->is_default)
                                <form action="{{ route('tax-rates.set-default', $taxRate) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check2-circle me-1"></i> Set as Default
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTaxRateModal">
                                    <i class="bi bi-trash me-1"></i> Delete Tax Rate
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tax Calculation</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-3">Example Calculations</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subtotal</th>
                                <th>Tax ({{ $taxRate->percentage }}%)</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $amounts = [100, 500, 1000, 5000, 10000];
                            @endphp
                            @foreach($amounts as $amount)
                                @php
                                    $tax = $taxRate->calculateTaxAmount($amount);
                                    $total = $amount + $tax;
                                @endphp
                                <tr>
                                    <td>{{ number_format($amount, 2) }} {{ $business->currency }}</td>
                                    <td>{{ number_format($tax, 2) }} {{ $business->currency }}</td>
                                    <td>{{ number_format($total, 2) }} {{ $business->currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle me-2"></i> About Tax Calculation
                    </h6>
                    <p class="mb-0">Tax amount is calculated by multiplying the subtotal by the tax rate percentage.</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Other Tax Rates</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($business->taxRates()->where('id', '!=', $taxRate->id)->take(5)->get() as $otherTaxRate)
                        <a href="{{ route('tax-rates.show', $otherTaxRate) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                {{ $otherTaxRate->name }}
                                @if($otherTaxRate->is_default)
                                    <span class="badge bg-success ms-2">Default</span>
                                @endif
                            </div>
                            <span>{{ $otherTaxRate->formatted_percentage }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="text-center p-3">
                    <a href="{{ route('tax-rates.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list me-1"></i> View All Tax Rates
                    </a>
                </div>
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
                
                @if($invoiceCount > 0 || $serviceCount > 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                        This tax rate is currently being used by {{ $invoiceCount }} invoice(s) and {{ $serviceCount }} service(s).
                        Deleting it may affect these items.
                    </div>
                @endif
                
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

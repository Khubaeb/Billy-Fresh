@extends('layouts.app')

@section('title', 'Tax Rates')
@section('page-title', 'Tax Rate Management')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        @if($business)
            <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">Tax Rates</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h5 class="mb-0">Tax Rates</h5>
            @if($business)
                <span class="badge bg-info ms-2">{{ $business->name }}</span>
            @endif
        </div>
        <div>
            @if($business)
                <a href="{{ route('tax-rates.create', ['business_id' => $business->id]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Tax Rate
                </a>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        @if($businesses->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> You don't have any businesses yet. Create a business first to manage tax rates.
                <div class="mt-3">
                    <a href="{{ route('businesses.create') }}" class="btn btn-sm btn-primary">Create Business</a>
                </div>
            </div>
        @elseif(!$business)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> Please select a business to view its tax rates.
            </div>
        @else
            <!-- Business Selector -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('tax-rates.index') }}" class="d-flex">
                        <select class="form-select me-2" name="business_id" onchange="this.form.submit()">
                            <option value="">Select Business</option>
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ $business && $business->id == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </form>
                </div>
            </div>

            @if($taxRates->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No tax rates found for this business.
                    <div class="mt-3">
                        <a href="{{ route('tax-rates.create', ['business_id' => $business->id]) }}" class="btn btn-sm btn-primary">
                            Create Tax Rate
                        </a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Rate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taxRates as $taxRate)
                                <tr>
                                    <td>
                                        <a href="{{ route('tax-rates.show', $taxRate) }}" class="text-decoration-none">
                                            {{ $taxRate->name }}
                                        </a>
                                    </td>
                                    <td>{{ $taxRate->formatted_percentage }}</td>
                                    <td>
                                        @if($taxRate->is_default)
                                            <span class="badge bg-success">Default</span>
                                        @else
                                            <span class="badge bg-secondary">Standard</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('tax-rates.show', $taxRate) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('tax-rates.edit', $taxRate) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(!$taxRate->is_default)
                                                <form action="{{ route('tax-rates.set-default', $taxRate) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Default">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteTaxRateModal-{{ $taxRate->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteTaxRateModal-{{ $taxRate->id }}" tabindex="-1" aria-labelledby="deleteTaxRateModalLabel-{{ $taxRate->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteTaxRateModalLabel-{{ $taxRate->id }}">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the tax rate <strong>{{ $taxRate->name }} ({{ $taxRate->formatted_percentage }})</strong>?</p>
                                                        <p class="text-danger">Warning: This action cannot be undone.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('tax-rates.destroy', $taxRate) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete Tax Rate</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i> About Tax Rates</h6>
                        <p class="mb-0">Tax rates are applied to invoices and services. The default tax rate is automatically selected when creating new invoices or services.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

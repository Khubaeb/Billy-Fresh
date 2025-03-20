@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Services</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row mb-4">
    <!-- Top Stats Cards -->
    <div class="col-md-4 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Total Services</h6>
                <h2 class="mb-0">{{ $stats['total'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Active</h6>
                <h2 class="mb-0">{{ $stats['active'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Inactive</h6>
                <h2 class="mb-0">{{ $stats['inactive'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Service Catalog</h5>
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Service
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form action="{{ route('services.index') }}" method="GET" class="d-flex">
                    <div class="input-group me-2">
                        <input type="text" name="search" class="form-control" placeholder="Search services..." value="{{ $search ?? '' }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="statusFilter" onchange="window.location.href='{{ route('services.index') }}?status='+this.value">
                    <option value="">All Status</option>
                    <option value="active" {{ isset($status) && $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ isset($status) && $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        @if($services->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> No services found. Get started by creating your first service.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Margin</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td>
                                    <a href="{{ route('services.show', $service) }}" class="text-decoration-none fw-medium">
                                        {{ $service->name }}
                                    </a>
                                </td>
                                <td>{{ $service->sku }}</td>
                                <td>${{ $service->formatted_price }}</td>
                                <td>${{ $service->cost ? number_format($service->cost, 2) : '-' }}</td>
                                <td>{{ $service->margin ? number_format($service->margin, 1) . '%' : '-' }}</td>
                                <td>
                                    @if($service->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('services.show', $service) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteServiceModal-{{ $service->id }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteServiceModal-{{ $service->id }}" tabindex="-1" aria-labelledby="deleteServiceModalLabel-{{ $service->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteServiceModalLabel-{{ $service->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    Are you sure you want to delete {{ $service->name }}? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
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
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

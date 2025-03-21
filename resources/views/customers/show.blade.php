@extends('layouts.app')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $customer->full_name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <!-- Customer Info Card -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Information</h5>
                <div>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Customer Name</h6>
                        <p class="fs-5 fw-medium mb-0">{{ $customer->full_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Status</h6>
                        <p class="mb-0">
                            @if($customer->status)
                                <span class="badge bg-{{ $customer->status == 'active' ? 'success' : ($customer->status == 'inactive' ? 'secondary' : 'primary') }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            @else
                                <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Email</h6>
                        <p class="mb-0">
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Phone</h6>
                        <p class="mb-0">
                            @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Company</h6>
                        <p class="mb-0">{{ $customer->company_name ?? 'Not provided' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Identification Number</h6>
                        <p class="mb-0">{{ $customer->identification_number ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Tax Number</h6>
                        <p class="mb-0">{{ $customer->tax_number ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Category</h6>
                        <p class="mb-0">{{ $customer->category ?? 'Not categorized' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Website</h6>
                        <p class="mb-0">
                            @if($customer->website)
                                <a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Next Contact Date</h6>
                        <p class="mb-0">
                            @if($customer->next_contact_date)
                                {{ $customer->next_contact_date->format('M d, Y') }}
                            @else
                                <span class="text-muted">Not scheduled</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Associated Business</h6>
                        <p class="mb-0">
                            @if($customer->business)
                                {{ $customer->business->name }}
                            @else
                                <span class="text-muted">Not associated</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Address Information</h5>
            </div>
            <div class="card-body">
                @if($customer->address_line1 || $customer->city || $customer->country)
                    <address class="mb-0">
                        @if($customer->address_line1)
                            {{ $customer->address_line1 }}<br>
                        @endif

                        @if($customer->address_line2)
                            {{ $customer->address_line2 }}<br>
                        @endif

                        @if($customer->city || $customer->state)
                            {{ $customer->city }}{{ $customer->city && $customer->state ? ', ' : '' }}{{ $customer->state }} {{ $customer->postal_code }}<br>
                        @endif

                        @if($customer->country)
                            {{ $customer->country }}
                        @endif
                    </address>
                @else
                    <p class="text-muted mb-0">No address information provided.</p>
                @endif
            </div>
        </div>

        <!-- Notes Card -->
        @if($customer->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Related Info Sidebar -->
    <div class="col-md-4">
        <!-- Invoices Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoices</h5>
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Invoice
                </a>
            </div>
            <div class="card-body">
                @if($customer->invoices && $customer->invoices->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($customer->invoices->take(5) as $invoice)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    <small class="d-block text-muted">{{ $invoice->invoice_date->format('M d, Y') }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                    <span class="ms-2">{{ number_format($invoice->total_amount, 2) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($customer->invoices->count() > 5)
                        <div class="mt-2 text-center">
                            <a href="#" class="text-decoration-none">View all invoices</a>
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">No invoices available.</p>
                @endif
            </div>
        </div>

        <!-- Activity Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">No recent activity recorded.</p>
                <!-- Activity logs will be listed here when implemented -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCustomerModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete {{ $customer->full_name }}? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('customers.destroy', $customer) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

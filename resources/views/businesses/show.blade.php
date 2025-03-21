@extends('layouts.app')

@section('title', $business->name)
@section('page-title', $business->name)

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.index') }}">Businesses</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $business->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Business Details -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Business Details</h5>
                <div>
                    <a href="{{ route('businesses.edit', $business) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-2">
                        @if($business->logo_path)
                            <img src="{{ asset('storage/' . $business->logo_path) }}" alt="{{ $business->name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bi bi-building fs-1 text-secondary"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-10">
                        <h4>{{ $business->name }}</h4>
                        @if($business->business_id)
                            <p class="text-muted mb-0">Business ID: {{ $business->business_id }}</p>
                        @endif
                        @if(!$business->is_active)
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        <p><strong>Email:</strong> {{ $business->email ?? 'Not specified' }}</p>
                        <p><strong>Phone:</strong> {{ $business->phone ?? 'Not specified' }}</p>
                        <p><strong>Website:</strong> {!! $business->website ? "<a href=\"{$business->website}\" target=\"_blank\">{$business->website}</a>" : 'Not specified' !!}</p>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Business Address</h6>
                        <address>
                            {{ $business->address_line1 ?? '' }}<br>
                            @if($business->address_line2){{ $business->address_line2 }}<br>@endif
                            @if($business->city || $business->state || $business->postal_code)
                                {{ $business->city ?? '' }} {{ $business->state ?? '' }} {{ $business->postal_code ?? '' }}<br>
                            @endif
                            {{ $business->country ?? '' }}
                        </address>
                    </div>

                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Business Registration</h6>
                        <p><strong>Tax Number:</strong> {{ $business->tax_number ?? 'Not specified' }}</p>
                        <p><strong>Registration Number:</strong> {{ $business->registration_number ?? 'Not specified' }}</p>
                        <p><strong>Currency:</strong> {{ $business->currency }}</p>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Invoice Settings</h6>
                        <p><strong>Invoice Prefix:</strong> {{ $business->getSetting('invoice_prefix') ?? 'Not specified' }}</p>
                        <p><strong>Next Invoice Number:</strong> {{ $business->getSetting('next_invoice_number') ?? '1' }}</p>
                        <p><strong>Default Due Days:</strong> {{ $business->getSetting('invoice_due_days') ?? '30' }} days</p>
                        <p><strong>Default Tax Rate:</strong> {{ $business->getSetting('default_tax_rate') ?? '0' }}{{ $business->getSetting('default_tax_type') == 'percentage' ? '%' : '' }}</p>
                    </div>
                </div>

                @if($business->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Notes</h6>
                            <p>{{ $business->notes }}</p>
                        </div>
                    </div>
                @endif

                <div class="row mt-4">
                    <div class="col-12">
                        <a href="{{ route('businesses.edit', $business) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Business
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBusinessModal">
                            <i class="bi bi-trash me-1"></i> Delete Business
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Business Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h6 class="text-white-50">Customers</h6>
                                <h2 class="mb-0">{{ $stats['customer_count'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h6 class="text-white-50">Services</h6>
                                <h2 class="mb-0">{{ $stats['service_count'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h6 class="text-white-50">Invoices</h6>
                                <h2 class="mb-0">{{ $stats['invoice_count'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h6 class="text-dark-50">Revenue</h6>
                                <h2 class="mb-0">{{ number_format($stats['invoice_total'], 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="border-bottom pb-2 mb-3">Invoice Status</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Paid:</span>
                                    <span class="text-success">{{ number_format($stats['invoice_paid'], 2) }} {{ $business->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Overdue:</span>
                                    <span class="text-danger">{{ number_format($stats['invoice_overdue'], 2) }} {{ $business->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Pending:</span>
                                    <span>{{ number_format($stats['invoice_total'] - $stats['invoice_paid'] - $stats['invoice_overdue'], 2) }} {{ $business->currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="border-bottom pb-2 mb-3">Quick Links</h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('customers.index', ['business_id' => $business->id]) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-people me-1"></i> View Customers
                                    </a>
                                    <a href="{{ route('invoices.index', ['business_id' => $business->id]) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-receipt me-1"></i> View Invoices
                                    </a>
                                    <a href="{{ route('services.index', ['business_id' => $business->id]) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-tools me-1"></i> View Services
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Business Users -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Business Users</h5>
                @can('manageUsers', $business)
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageUsersModal">
                        <i class="bi bi-plus me-1"></i> Manage
                    </button>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($business->users as $user)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-{{ $user->pivot->role_id == 1 ? 'primary' : 'secondary' }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                            <span class="badge bg-{{ $user->pivot->role_id == 1 ? 'primary' : 'secondary' }}">
                                {{ $user->pivot->role_id == 1 ? 'Admin' : 'User' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(count($business->invoices) > 0)
                        @foreach($business->invoices->sortByDesc('created_at')->take(5) as $invoice)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>Invoice #{{ $invoice->invoice_number }} created</span>
                                    <small class="text-muted">{{ $invoice->created_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted">{{ $invoice->customer->full_name }} - {{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</small>
                            </div>
                        @endforeach
                    @else
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-info-circle d-block mb-2 fs-4"></i>
                            No recent activity to display
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Business Modal -->
<div class="modal fade" id="deleteBusinessModal" tabindex="-1" aria-labelledby="deleteBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBusinessModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $business->name }}</strong>?</p>
                <p class="text-danger">Warning: This action is irreversible and will remove all business data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('businesses.destroy', $business) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Business</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Manage Users Modal -->
@can('manageUsers', $business)
<div class="modal fade" id="manageUsersModal" tabindex="-1" aria-labelledby="manageUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageUsersModalLabel">Manage Business Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('businesses.update', $business) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Select users to add to this business:</p>
                    
                    <div class="mb-3">
                        <label for="users" class="form-label">Users</label>
                        <div class="row">
                            @foreach($business->users as $existingUser)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="users[]" value="{{ $existingUser->id }}" id="user-{{ $existingUser->id }}" checked {{ $existingUser->id == Auth::id() ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="user-{{ $existingUser->id }}">
                                            {{ $existingUser->name }} ({{ $existingUser->email }})
                                            @if($existingUser->id == Auth::id())
                                                <span class="text-muted">(You)</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                            
                            @foreach(App\Models\User::whereNotIn('id', $business->users->pluck('id'))->get() as $availableUser)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="users[]" value="{{ $availableUser->id }}" id="user-{{ $availableUser->id }}">
                                        <label class="form-check-label" for="user-{{ $availableUser->id }}">
                                            {{ $availableUser->name }} ({{ $availableUser->email }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #007bff;
    border-radius: 50%;
    color: white;
    text-align: center;
    line-height: 40px;
    font-weight: 600;
    font-size: 16px;
}
</style>
@endsection

@extends('layouts.app')

@section('title', 'Recurring Billings')
@section('page-title', 'Recurring Billings')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Recurring Billings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recurring Billing Summary</h5>
        <a href="{{ route('recurring.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Recurring Billing
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Recurring</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="text-white-50">Active</h6>
                        <h3 class="mb-0">{{ $stats['active'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="text-dark-50">Due This Month</h6>
                        <h3 class="mb-0">{{ $stats['due_this_month'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h6 class="text-white-50">Monthly Revenue</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_monthly_revenue'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recurring Billings</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <form action="{{ route('recurring.index') }}" method="GET" class="bg-light p-3 rounded">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="">All Frequencies</option>
                                <option value="daily" {{ request('frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ request('frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ request('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ request('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ request('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->full_name }} {{ $customer->company_name ? '(' . $customer->company_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Next Billing From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Next Billing To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <div class="d-grid gap-2">
                                <a href="{{ route('recurring.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($recurringBillings->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> No recurring billings found. Get started by creating your first recurring billing.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Next Billing</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recurringBillings as $recurring)
                            <tr>
                                <td>
                                    <a href="{{ route('recurring.show', $recurring) }}" class="text-decoration-none">
                                        {{ $recurring->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('customers.show', $recurring->customer) }}" class="text-decoration-none">
                                        {{ $recurring->customer->full_name }}
                                    </a>
                                    @if($recurring->customer->company_name)
                                        <div class="small text-muted">{{ $recurring->customer->company_name }}</div>
                                    @endif
                                </td>
                                <td>{{ number_format($recurring->amount, 2) }} {{ $recurring->currency }}</td>
                                <td>
                                    {{ ucfirst($recurring->frequency) }}
                                    @if($recurring->interval > 1)
                                        (every {{ $recurring->interval }} {{ Str::plural(rtrim($recurring->frequency, 'ly'), $recurring->interval) }})
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $recurring->next_billing_date && $recurring->next_billing_date->isPast() ? 'text-danger' : '' }}">
                                        {{ $recurring->next_billing_date ? $recurring->next_billing_date->format('M d, Y') : 'N/A' }}
                                    </span>
                                    @if($recurring->next_billing_date && $recurring->next_billing_date->isPast())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @elseif($recurring->next_billing_date && $recurring->next_billing_date->isToday())
                                        <span class="badge bg-warning text-dark ms-1">Today</span>
                                    @elseif($recurring->next_billing_date && $recurring->next_billing_date->diffInDays(now()) <= 7)
                                        <span class="badge bg-info text-white ms-1">Soon</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $recurring->status == 'active' ? 'success' : ($recurring->status == 'paused' ? 'warning' : ($recurring->status == 'completed' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst($recurring->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('recurring.show', $recurring) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('recurring.edit', $recurring) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($recurring->status == 'active')
                                            <form action="{{ route('recurring.update-status', $recurring) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="paused">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Pause">
                                                    <i class="bi bi-pause-fill"></i>
                                                </button>
                                            </form>
                                        @elseif($recurring->status == 'paused')
                                            <form action="{{ route('recurring.update-status', $recurring) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Resume">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteRecurringModal-{{ $recurring->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteRecurringModal-{{ $recurring->id }}" tabindex="-1" aria-labelledby="deleteRecurringModalLabel-{{ $recurring->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteRecurringModalLabel-{{ $recurring->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the recurring billing "{{ $recurring->name }}"? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('recurring.destroy', $recurring) }}" method="POST">
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

            <!-- Pagination -->
            <div class="mt-4">
                {{ $recurringBillings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

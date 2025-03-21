@extends('layouts.app')

@section('title', 'Recurring Billing Details')
@section('page-title', 'Recurring Billing Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('recurring.index') }}">Recurring Billings</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $recurring->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Main Recurring Billing Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recurring Billing Information</h5>
                <div>
                    @if($recurring->status == 'active')
                        <form action="{{ route('recurring.update-status', $recurring) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="paused">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-pause-fill me-1"></i> Pause
                            </button>
                        </form>
                    @elseif($recurring->status == 'paused')
                        <form action="{{ route('recurring.update-status', $recurring) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-play-fill me-1"></i> Activate
                            </button>
                        </form>
                    @endif
                    
                    @if($recurring->status != 'cancelled' && $recurring->status != 'completed')
                        <form action="{{ route('recurring.update-status', $recurring) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('recurring.edit', $recurring) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteRecurringModal">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Banner -->
                <div class="mb-4">
                    @if($recurring->status == 'active')
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Active</strong> - Next billing will occur on {{ $recurring->next_billing_date->format('M d, Y') }}
                                @if($recurring->next_billing_date->isPast())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @elseif($recurring->next_billing_date->isToday())
                                    <span class="badge bg-warning text-dark ms-2">Today</span>
                                @elseif($recurring->next_billing_date->diffInDays(now()) <= 7)
                                    <span class="badge bg-info ms-2">Soon</span>
                                @endif
                            </div>
                        </div>
                    @elseif($recurring->status == 'paused')
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bi bi-pause-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Paused</strong> - This recurring billing is currently paused. No automatic billing will occur.
                            </div>
                        </div>
                    @elseif($recurring->status == 'completed')
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-check-all me-2 fs-5"></i>
                            <div>
                                <strong>Completed</strong> - This recurring billing has been completed.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary d-flex align-items-center" role="alert">
                            <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Cancelled</strong> - This recurring billing has been cancelled.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-8">
                        <h4>{{ $recurring->name }}</h4>
                        @if($recurring->description)
                            <p class="text-muted">{{ $recurring->description }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h3 class="text-primary mb-0">{{ number_format($recurring->amount, 2) }} {{ $recurring->currency }}</h3>
                        <div class="text-muted">
                            {{ ucfirst($recurring->frequency) }}
                            @if($recurring->interval > 1)
                                (every {{ $recurring->interval }} {{ Str::plural(rtrim($recurring->frequency, 'ly'), $recurring->interval) }})
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Customer</h6>
                        <p class="mb-1">
                            <a href="{{ route('customers.show', $recurring->customer) }}" class="text-decoration-none">
                                {{ $recurring->customer->full_name }}
                            </a>
                        </p>
                        @if($recurring->customer->company_name)
                            <p class="mb-1">{{ $recurring->customer->company_name }}</p>
                        @endif
                        @if($recurring->customer->email)
                            <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $recurring->customer->email }}</p>
                        @endif
                        @if($recurring->customer->phone)
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $recurring->customer->phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Business</h6>
                        <p class="mb-1">{{ $recurring->business->name }}</p>
                        @if($recurring->business->email)
                            <p class="mb-1">{{ $recurring->business->email }}</p>
                        @endif
                        @if($recurring->business->address)
                            <p class="mb-1">{{ $recurring->business->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Billing Schedule</h6>
                        <p class="mb-1"><strong>Start Date:</strong> {{ $recurring->start_date->format('M d, Y') }}</p>
                        @if($recurring->end_date)
                            <p class="mb-1"><strong>End Date:</strong> {{ $recurring->end_date->format('M d, Y') }}</p>
                        @else
                            <p class="mb-1"><strong>End Date:</strong> No end date (ongoing)</p>
                        @endif
                        <p class="mb-1"><strong>Next Billing:</strong> 
                            @if($recurring->status == 'active')
                                <span class="{{ $recurring->next_billing_date->isPast() ? 'text-danger' : '' }}">
                                    {{ $recurring->next_billing_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="text-muted">N/A ({{ ucfirst($recurring->status) }})</span>
                            @endif
                        </p>
                        @if($recurring->last_billed_date)
                            <p class="mb-1"><strong>Last Billed:</strong> {{ $recurring->last_billed_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Payment Information</h6>
                        @if($recurring->service)
                            <p class="mb-1"><strong>Service:</strong> {{ $recurring->service->name }}</p>
                        @endif
                        @if($recurring->paymentMethod)
                            <p class="mb-1"><strong>Payment Method:</strong> {{ $recurring->paymentMethod->name }}</p>
                        @endif
                        <p class="mb-1"><strong>Billing Count:</strong> {{ $recurring->billing_count }} time(s)</p>
                    </div>
                </div>

                @if($recurring->notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-muted">Notes</h6>
                            <p class="mb-1">{{ $recurring->notes }}</p>
                        </div>
                    </div>
                @endif

                @if($recurring->status == 'active')
                    <div class="mt-4">
                        <form action="{{ route('recurring.generate-invoice', $recurring) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-receipt me-1"></i> Generate Invoice Now
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <!-- Timeline Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Billing Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($recurring->start_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Started</h6>
                                <div class="text-muted small">{{ $recurring->start_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                    @endif

                    @if($recurring->last_billed_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Last Billed</h6>
                                <div class="text-muted small">{{ $recurring->last_billed_date->format('M d, Y') }}</div>
                                <div class="text-muted small">Billing #{{ $recurring->billing_count }}</div>
                            </div>
                        </div>
                    @endif

                    @if($recurring->status == 'active')
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $recurring->next_billing_date->isPast() ? 'bg-danger' : 'bg-warning' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Next Billing</h6>
                                <div class="text-muted small">{{ $recurring->next_billing_date->format('M d, Y') }}</div>
                                <div class="text-muted small">
                                    @if($recurring->next_billing_date->isPast())
                                        <span class="text-danger">Overdue ({{ $recurring->next_billing_date->diffForHumans() }})</span>
                                    @else
                                        {{ $recurring->next_billing_date->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($recurring->end_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">End Date</h6>
                                <div class="text-muted small">{{ $recurring->end_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Generated Invoices Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Generated Invoices</h5>
                <span class="badge bg-primary">{{ $recurring->invoices->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($recurring->invoices->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-receipt fs-4 d-block mb-2"></i>
                        No invoices have been generated yet.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recurring->invoices as $invoice)
                            <a href="{{ route('invoices.show', $invoice) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-medium">{{ $invoice->invoice_number }}</div>
                                    <div class="text-muted small">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</div>
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'partially_paid' ? 'warning' : ($invoice->status == 'sent' ? 'info' : 'secondary'))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteRecurringModal" tabindex="-1" aria-labelledby="deleteRecurringModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRecurringModalLabel">Confirm Delete</h5>
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
@endsection

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0 0 0 1rem;
    color: #6c757d;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    width: 15px;
    height: 15px;
    left: -1.5rem;
    top: 0;
    border-radius: 100%;
}

.timeline-item:not(:last-child) .timeline-marker:before {
    content: "";
    width: 1px;
    height: calc(100% - 15px);
    position: absolute;
    left: 7px;
    top: 15px;
    background-color: #dee2e6;
}
</style>
@endsection

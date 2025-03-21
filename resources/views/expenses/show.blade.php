@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $expense->description }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Main Expense Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Expense Details</h5>
                <div>
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteExpenseModal">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-7">
                        <h4>{{ $expense->description }}</h4>
                        <div class="mb-2">
                            @if($expense->is_billable)
                                <span class="badge bg-success me-2">Billable</span>
                            @endif
                            @if($expense->is_reimbursable)
                                <span class="badge bg-warning text-dark me-2">Reimbursable</span>
                            @endif
                            <span class="badge bg-{{ $expense->status == 'completed' ? 'success' : ($expense->status == 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </div>
                        <p class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i> {{ $expense->expense_date->format('F d, Y') }}
                            @if($expense->vendor_name)
                                <span class="ms-3"><i class="bi bi-shop me-1"></i> {{ $expense->vendor_name }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <h3 class="text-primary mb-1">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</h3>
                        <p class="text-muted">
                            @if($expense->reference)
                                <span><i class="bi bi-hash me-1"></i> Ref: {{ $expense->reference }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">Category</label>
                            <div>{{ $expense->category->name ?? 'None' }}</div>
                        </div>
                        @if($expense->business)
                        <div class="mb-3">
                            <label class="text-muted">Business</label>
                            <div>{{ $expense->business->name }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($expense->customer)
                        <div class="mb-3">
                            <label class="text-muted">Customer</label>
                            <div>
                                <a href="{{ route('customers.show', $expense->customer) }}">
                                    {{ $expense->customer->full_name }}
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($expense->invoice)
                        <div class="mb-3">
                            <label class="text-muted">Invoice</label>
                            <div>
                                <a href="{{ route('invoices.show', $expense->invoice) }}">
                                    {{ $expense->invoice->invoice_number }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($expense->payment_method)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">Payment Method</label>
                            <div>{{ $expense->payment_method }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($expense->notes)
                <hr>
                <div class="mb-3">
                    <label class="text-muted">Notes</label>
                    <div>{{ $expense->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Receipt Card -->
        @if($expense->receipt_path)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Receipt</h5>
            </div>
            <div class="card-body text-center">
                <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank">
                    <img src="{{ asset('storage/' . $expense->receipt_path) }}" class="img-fluid border rounded" alt="Receipt Image">
                </a>
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-download me-1"></i> Download Receipt
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Related Items Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Related Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @if($expense->is_billable)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Billable Status</span>
                            <span class="badge bg-success">Billable</span>
                        </li>
                        @if($expense->invoice)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Billed to Invoice</span>
                                <a href="{{ route('invoices.show', $expense->invoice) }}">
                                    {{ $expense->invoice->invoice_number }}
                                </a>
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Billed to Invoice</span>
                                <span class="text-muted">Not billed yet</span>
                            </li>
                        @endif
                    @endif
                    
                    @if($expense->is_reimbursable)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Reimbursement Status</span>
                            <span class="badge bg-warning text-dark">Reimbursable</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Reimbursed</span>
                            <span>{{ $expense->status == 'completed' ? 'Yes' : 'No' }}</span>
                        </li>
                    @endif
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Created By</span>
                        <span>{{ $expense->user->name ?? 'Unknown' }}</span>
                    </li>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Created At</span>
                        <span>{{ $expense->created_at->format('M d, Y g:i A') }}</span>
                    </li>
                    
                    @if($expense->updated_at && $expense->updated_at->ne($expense->created_at))
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Last Updated</span>
                            <span>{{ $expense->updated_at->format('M d, Y g:i A') }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExpenseModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the expense "{{ $expense->description }}"? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('expenses.destroy', $expense) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

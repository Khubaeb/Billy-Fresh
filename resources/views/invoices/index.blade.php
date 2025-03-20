@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Invoices</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row mb-4">
    <!-- Top Stats Cards -->
    <div class="col-md-3 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Total Invoices</h6>
                <h2 class="mb-0">{{ $stats['total'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Draft</h6>
                <h2 class="mb-0">{{ $stats['draft'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Sent</h6>
                <h2 class="mb-0">{{ $stats['sent'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Paid</h6>
                <h2 class="mb-0">{{ $stats['paid'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Invoice List</h5>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Invoice
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-9">
                <form action="{{ route('invoices.index') }}" method="GET" class="d-flex">
                    <div class="input-group me-2">
                        <input type="text" name="search" class="form-control" placeholder="Search invoices..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter" onchange="window.location.href='{{ route('invoices.index') }}?status='+this.value">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="partially_paid" {{ $status == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>

        @if($invoices->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> No invoices found. Get started by creating your first invoice.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none fw-medium">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->invoice_date->format('m/d/Y') }}</td>
                                <td>{{ $invoice->due_date->format('m/d/Y') }}</td>
                                <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    @if($invoice->status == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice->status == 'draft')
                                        <span class="badge bg-secondary">Draft</span>
                                    @elseif($invoice->status == 'sent')
                                        <span class="badge bg-primary">Sent</span>
                                    @elseif($invoice->status == 'overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                    @elseif($invoice->status == 'partially_paid')
                                        <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                        <span class="badge bg-dark">Cancelled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteInvoiceModal-{{ $invoice->id }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteInvoiceModal-{{ $invoice->id }}" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel-{{ $invoice->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteInvoiceModalLabel-{{ $invoice->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    Are you sure you want to delete invoice {{ $invoice->invoice_number }}? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline">
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
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $invoice->invoice_number }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Invoice #{{ $invoice->invoice_number }}</h5>
        <div>
            @if($invoice->status == 'draft')
                <form action="{{ route('invoices.mark-as-sent', $invoice) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-envelope me-1"></i> Mark as Sent
                    </button>
                </form>
            @endif
            
            @if($invoice->status != 'paid' && $invoice->amount_due > 0)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="bi bi-cash me-1"></i> Record Payment
                </button>
            @endif
            
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Invoice Status Banner -->
        <div class="mb-4">
            @if($invoice->status == 'paid')
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Paid</strong> - This invoice was paid on {{ $invoice->paid_at->format('M d, Y') }}
                    </div>
                </div>
            @elseif($invoice->status == 'overdue')
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Overdue</strong> - This invoice was due on {{ $invoice->due_date->format('M d, Y') }} ({{ $invoice->due_date->diffForHumans() }})
                    </div>
                </div>
            @elseif($invoice->status == 'partially_paid')
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Partially Paid</strong> - {{ number_format($invoice->amount_paid, 2) }} 
                        {{ $invoice->currency }} has been paid. {{ number_format($invoice->amount_due, 2) }} 
                        {{ $invoice->currency }} is still due by {{ $invoice->due_date->format('M d, Y') }}.
                    </div>
                </div>
            @elseif($invoice->status == 'sent')
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-envelope-fill me-2 fs-5"></i>
                    <div>
                        <strong>Sent</strong> - This invoice was sent on {{ $invoice->sent_at->format('M d, Y') }} and is due by {{ $invoice->due_date->format('M d, Y') }}.
                    </div>
                </div>
            @else
                <div class="alert alert-secondary d-flex align-items-center" role="alert">
                    <i class="bi bi-pencil-fill me-2 fs-5"></i>
                    <div>
                        <strong>Draft</strong> - This invoice has not been sent yet.
                    </div>
                </div>
            @endif
        </div>

        <div class="row mb-4">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">From</h6>
                        <h5 class="mb-1">{{ $invoice->business ? $invoice->business->name : auth()->user()->name }}</h5>
                        @if($invoice->business)
                            <p class="mb-0">{{ $invoice->business->address ?? '' }}</p>
                            @if($invoice->business->city || $invoice->business->state || $invoice->business->postal_code)
                                <p class="mb-0">
                                    {{ $invoice->business->city ?? '' }}
                                    {{ $invoice->business->city && $invoice->business->state ? ', ' : '' }}
                                    {{ $invoice->business->state ?? '' }} {{ $invoice->business->postal_code ?? '' }}
                                </p>
                            @endif
                            <p class="mb-0">{{ $invoice->business->country ?? '' }}</p>
                            @if($invoice->business->tax_number)
                                <p class="mb-0 mt-2">Tax Number: {{ $invoice->business->tax_number }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Bill To</h6>
                        <h5 class="mb-1">{{ $invoice->customer->full_name }}</h5>
                        <p class="mb-0">{{ $invoice->customer->company_name ?? '' }}</p>
                        <p class="mb-0">{{ $invoice->customer->address_line1 ?? '' }}</p>
                        @if($invoice->customer->address_line2)
                            <p class="mb-0">{{ $invoice->customer->address_line2 }}</p>
                        @endif
                        @if($invoice->customer->city || $invoice->customer->state || $invoice->customer->postal_code)
                            <p class="mb-0">
                                {{ $invoice->customer->city ?? '' }}
                                {{ $invoice->customer->city && $invoice->customer->state ? ', ' : '' }}
                                {{ $invoice->customer->state ?? '' }} {{ $invoice->customer->postal_code ?? '' }}
                            </p>
                        @endif
                        <p class="mb-0">{{ $invoice->customer->country ?? '' }}</p>
                        @if($invoice->customer->tax_number)
                            <p class="mb-0 mt-2">Tax Number: {{ $invoice->customer->tax_number }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Item / Description</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Tax</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $item->name }}</div>
                                            @if($item->description)
                                                <div class="text-muted small">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">
                                            @if($item->tax_rate > 0)
                                                {{ number_format($item->tax_rate, 2) }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-medium">Subtotal:</td>
                                        <td class="text-end">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    @if($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="4" class="text-end">Tax:</td>
                                        <td class="text-end">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    @endif
                                    @if($invoice->discount_amount > 0)
                                    <tr>
                                        <td colspan="4" class="text-end">Discount:</td>
                                        <td class="text-end">-{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    @if($invoice->amount_paid > 0)
                                    <tr>
                                        <td colspan="4" class="text-end">Amount Paid:</td>
                                        <td class="text-end">{{ number_format($invoice->amount_paid, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-medium">Amount Due:</td>
                                        <td class="text-end fw-medium">{{ number_format($invoice->amount_due, 2) }} {{ $invoice->currency }}</td>
                                    </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Invoice Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Invoice Number:</div>
                            <div class="col-md-7">{{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Invoice Date:</div>
                            <div class="col-md-7">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Due Date:</div>
                            <div class="col-md-7">{{ $invoice->due_date->format('M d, Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Status:</div>
                            <div class="col-md-7">
                                <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'partially_paid' ? 'warning' : ($invoice->status == 'sent' ? 'info' : 'secondary'))) }}">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </div>
                        </div>
                        @if($invoice->reference)
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Reference:</div>
                            <div class="col-md-7">{{ $invoice->reference }}</div>
                        </div>
                        @endif
                        @if($invoice->paymentMethod)
                        <div class="row mb-2">
                            <div class="col-md-5 text-muted">Payment Method:</div>
                            <div class="col-md-7">{{ $invoice->paymentMethod->name }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Notes section -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Notes</h6>
                    </div>
                    <div class="card-body">
                        @if($invoice->notes)
                            <p class="mb-0">{{ $invoice->notes }}</p>
                        @else
                            <p class="text-muted mb-0">No notes provided</p>
                        @endif
                    </div>
                </div>
                
                <!-- Terms section -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Terms & Conditions</h6>
                    </div>
                    <div class="card-body">
                        @if($invoice->terms)
                            <p class="mb-0">{{ $invoice->terms }}</p>
                        @else
                            <p class="text-muted mb-0">No terms specified</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Payment History</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td>{{ $payment->reference ?? '-' }}</td>
                                        <td>{{ $payment->payment_method }}</td>
                                        <td class="text-end">{{ number_format($payment->amount, 2) }} {{ $invoice->currency }}</td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.record-payment', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="recordPaymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $invoice->currency }}</span>
                            <input type="number" step="0.01" min="0.01" max="{{ $invoice->amount_due }}" class="form-control" id="amount" name="amount" value="{{ $invoice->amount_due }}" required>
                        </div>
                        <div class="form-text">Maximum: {{ number_format($invoice->amount_due, 2) }} {{ $invoice->currency }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">-- Select Method --</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Check">Check</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="Transaction ID, Check Number, etc.">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInvoiceModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete Invoice #{{ $invoice->invoice_number }}? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

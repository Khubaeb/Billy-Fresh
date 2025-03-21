@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.show', $expense) }}">{{ $expense->description }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Expense</h5>
        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Expense
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $expense->description) }}" required>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                    @error('expense_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-select w-25 @error('currency') is-invalid @enderror" id="currency" name="currency">
                            <option value="USD" {{ old('currency', $expense->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency', $expense->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ old('currency', $expense->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                            <option value="CAD" {{ old('currency', $expense->currency) == 'CAD' ? 'selected' : '' }}>CAD</option>
                            <option value="AUD" {{ old('currency', $expense->currency) == 'AUD' ? 'selected' : '' }}>AUD</option>
                        </select>
                        <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
                    </div>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="expense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="vendor_name" class="form-label">Vendor/Merchant</label>
                    <input type="text" class="form-control @error('vendor_name') is-invalid @enderror" id="vendor_name" name="vendor_name" value="{{ old('vendor_name', $expense->vendor_name) }}">
                    @error('vendor_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="reference" class="form-label">Reference Number</label>
                    <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference', $expense->reference) }}" placeholder="Receipt, Invoice, or Transaction #">
                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                        <option value="">-- Select Payment Method --</option>
                        <option value="Cash" {{ old('payment_method', $expense->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Credit Card" {{ old('payment_method', $expense->payment_method) == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="Debit Card" {{ old('payment_method', $expense->payment_method) == 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
                        <option value="Bank Transfer" {{ old('payment_method', $expense->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="Check" {{ old('payment_method', $expense->payment_method) == 'Check' ? 'selected' : '' }}>Check</option>
                        <option value="PayPal" {{ old('payment_method', $expense->payment_method) == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                        <option value="Other" {{ old('payment_method', $expense->payment_method) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="completed" {{ old('status', $expense->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ old('status', $expense->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ old('status', $expense->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="business_id" class="form-label">Business</label>
                    <select class="form-select @error('business_id') is-invalid @enderror" id="business_id" name="business_id">
                        <option value="">-- Select Business --</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ old('business_id', $expense->business_id) == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('business_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="receipt_image" class="form-label">Receipt Image</label>
                    <input type="file" class="form-control @error('receipt_image') is-invalid @enderror" id="receipt_image" name="receipt_image">
                    <div class="form-text">Max file size: 5MB. Supported formats: JPG, PNG, PDF.</div>
                    @if($expense->receipt_path)
                        <div class="mt-2">
                            <small class="text-muted">Current receipt: <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank">View</a></small>
                        </div>
                    @endif
                    @error('receipt_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input @error('is_billable') is-invalid @enderror" type="checkbox" id="is_billable" name="is_billable" value="1" {{ old('is_billable', $expense->is_billable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_billable">
                            Billable to Customer
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input @error('is_reimbursable') is-invalid @enderror" type="checkbox" id="is_reimbursable" name="is_reimbursable" value="1" {{ old('is_reimbursable', $expense->is_reimbursable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_reimbursable">
                            Reimbursable
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mb-3 billable-options" style="{{ old('is_billable', $expense->is_billable) ? '' : 'display: none;' }}">
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $expense->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->full_name }} {{ $customer->company_name ? '(' . $customer->company_name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="invoice_id" class="form-label">Invoice</label>
                    <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id">
                        <option value="">-- Select Invoice --</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}" {{ old('invoice_id', $expense->invoice_id) == $invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_number }} ({{ $invoice->customer->full_name }})
                            </option>
                        @endforeach
                    </select>
                    @error('invoice_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $expense->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Expense
                    </button>
                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary ms-2">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isBillableCheckbox = document.getElementById('is_billable');
        const billableOptions = document.querySelector('.billable-options');
        
        isBillableCheckbox.addEventListener('change', function() {
            if (this.checked) {
                billableOptions.style.display = 'flex';
            } else {
                billableOptions.style.display = 'none';
            }
        });
    });
</script>
@endsection

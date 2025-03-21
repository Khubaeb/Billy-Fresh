@extends('layouts.app')

@section('title', 'Edit Recurring Billing')
@section('page-title', 'Edit Recurring Billing')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('recurring.index') }}">Recurring Billings</a></li>
        <li class="breadcrumb-item"><a href="{{ route('recurring.show', $recurring) }}">{{ $recurring->name }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Recurring Billing</h5>
        <a href="{{ route('recurring.show', $recurring) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Details
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('recurring.update', $recurring) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <h6 class="text-primary mb-3">Basic Information</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $recurring->name) }}" required>
                        <div class="form-text">A descriptive name for this recurring billing, e.g. "Monthly Website Hosting"</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $recurring->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $recurring->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                    <option value="USD" {{ old('currency', $recurring->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ old('currency', $recurring->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="GBP" {{ old('currency', $recurring->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                                    <option value="CAD" {{ old('currency', $recurring->currency) == 'CAD' ? 'selected' : '' }}>CAD</option>
                                    <option value="AUD" {{ old('currency', $recurring->currency) == 'AUD' ? 'selected' : '' }}>AUD</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $recurring->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ old('status', $recurring->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                            <option value="completed" {{ old('status', $recurring->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $recurring->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $recurring->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                        <div class="form-text">Uncheck to temporarily disable this recurring billing</div>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Customer & Business -->
            <h6 class="text-primary mb-3">Customer & Business</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $recurring->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->full_name }} {{ $customer->company_name ? '(' . $customer->company_name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="business_id" class="form-label">Business <span class="text-danger">*</span></label>
                        <select class="form-select @error('business_id') is-invalid @enderror" id="business_id" name="business_id" required>
                            <option value="">-- Select Business --</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" {{ old('business_id', $recurring->business_id) == $business->id ? 'selected' : '' }}>
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('business_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Billing Schedule -->
            <h6 class="text-primary mb-3">Billing Schedule</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select class="form-select @error('frequency') is-invalid @enderror" id="frequency" name="frequency" required>
                            <option value="daily" {{ old('frequency', $recurring->frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('frequency', $recurring->frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('frequency', $recurring->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency', $recurring->frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency', $recurring->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                        @error('frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="interval" class="form-label">Every <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" min="1" class="form-control @error('interval') is-invalid @enderror" id="interval" name="interval" value="{{ old('interval', $recurring->interval) }}" required>
                            <span class="input-group-text" id="interval-label">
                                {{ $recurring->frequency == 'daily' ? 'day(s)' : 
                                  ($recurring->frequency == 'weekly' ? 'week(s)' : 
                                   ($recurring->frequency == 'monthly' ? 'month(s)' : 
                                    ($recurring->frequency == 'quarterly' ? 'quarter(s)' : 'year(s)'))) }}
                            </span>
                        </div>
                        <div class="form-text">How often to bill (e.g., every 1 month, every 3 months, etc.)</div>
                        @error('interval')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $recurring->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $recurring->end_date ? $recurring->end_date->format('Y-m-d') : '') }}">
                        <div class="form-text">Leave blank for no end date (ongoing)</div>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="next_billing_date" class="form-label">Next Billing Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('next_billing_date') is-invalid @enderror" id="next_billing_date" name="next_billing_date" value="{{ old('next_billing_date', $recurring->next_billing_date->format('Y-m-d')) }}" required>
                        @error('next_billing_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <h6 class="text-primary mb-3">Additional Details</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="service_id" class="form-label">Service</label>
                        <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id">
                            <option value="">-- Select Service (Optional) --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id', $recurring->service_id) == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ number_format($service->price, 2) }} {{ $service->currency }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_method_id" class="form-label">Payment Method</label>
                        <select class="form-select @error('payment_method_id') is-invalid @enderror" id="payment_method_id" name="payment_method_id">
                            <option value="">-- Select Payment Method (Optional) --</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('payment_method_id', $recurring->payment_method_id) == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $recurring->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('recurring.show', $recurring) }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Recurring Billing</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update the interval label based on the selected frequency
        const frequencySelect = document.getElementById('frequency');
        const intervalLabel = document.getElementById('interval-label');

        const updateIntervalLabel = () => {
            const frequency = frequencySelect.value;
            switch(frequency) {
                case 'daily':
                    intervalLabel.textContent = 'day(s)';
                    break;
                case 'weekly':
                    intervalLabel.textContent = 'week(s)';
                    break;
                case 'monthly':
                    intervalLabel.textContent = 'month(s)';
                    break;
                case 'quarterly':
                    intervalLabel.textContent = 'quarter(s)';
                    break;
                case 'yearly':
                    intervalLabel.textContent = 'year(s)';
                    break;
                default:
                    intervalLabel.textContent = 'month(s)';
            }
        };

        // Set the initial label
        updateIntervalLabel();

        // Update label when frequency changes
        frequencySelect.addEventListener('change', updateIntervalLabel);
    });
</script>
@endsection

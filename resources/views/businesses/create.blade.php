@extends('layouts.app')

@section('title', 'Create Business')
@section('page-title', 'Create New Business')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.index') }}">Businesses</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create New Business</li>
    </ol>
</nav>
@endsection

@section('content')
<form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <label for="name" class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="business_id" class="form-label">Business ID</label>
                            <input type="text" class="form-control @error('business_id') is-invalid @enderror" id="business_id" name="business_id" value="{{ old('business_id') }}">
                            @error('business_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website') }}">
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tax_number" class="form-label">Tax Number</label>
                            <input type="text" class="form-control @error('tax_number') is-invalid @enderror" id="tax_number" name="tax_number" value="{{ old('tax_number') }}">
                            @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" value="{{ old('registration_number') }}">
                            @error('registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                                <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                <option value="JPY" {{ old('currency') == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                <option value="CNY" {{ old('currency') == 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="logo" class="form-label">Business Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo">
                            <div class="form-text">Max size: 5MB. Supported formats: JPG, PNG, GIF</div>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Business Address</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control @error('address_line1') is-invalid @enderror" id="address_line1" name="address_line1" value="{{ old('address_line1') }}">
                        @error('address_line1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control @error('address_line2') is-invalid @enderror" id="address_line2" name="address_line2" value="{{ old('address_line2') }}">
                        @error('address_line2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label">Postal/ZIP Code</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country') }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', 'INV-') }}">
                            <div class="form-text">Example: INV-, INVOICE-, etc.</div>
                            @error('invoice_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="next_invoice_number" class="form-label">Next Invoice Number</label>
                            <input type="number" min="1" class="form-control @error('next_invoice_number') is-invalid @enderror" id="next_invoice_number" name="next_invoice_number" value="{{ old('next_invoice_number', '1001') }}">
                            @error('next_invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="invoice_due_days" class="form-label">Default Due Days</label>
                            <input type="number" min="0" class="form-control @error('invoice_due_days') is-invalid @enderror" id="invoice_due_days" name="invoice_due_days" value="{{ old('invoice_due_days', '30') }}">
                            @error('invoice_due_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="default_tax_rate" class="form-label">Default Tax Rate (%)</label>
                            <input type="number" min="0" step="0.01" class="form-control @error('default_tax_rate') is-invalid @enderror" id="default_tax_rate" name="default_tax_rate" value="{{ old('default_tax_rate', '0') }}">
                            @error('default_tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="invoice_terms" class="form-label">Default Invoice Terms</label>
                        <textarea class="form-control @error('invoice_terms') is-invalid @enderror" id="invoice_terms" name="invoice_terms" rows="3">{{ old('invoice_terms', 'Payment is due within 30 days from the date of invoice. Thank you for your business.') }}</textarea>
                        @error('invoice_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="invoice_notes" class="form-label">Default Invoice Notes</label>
                        <textarea class="form-control @error('invoice_notes') is-invalid @enderror" id="invoice_notes" name="invoice_notes" rows="3">{{ old('invoice_notes') }}</textarea>
                        @error('invoice_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Additional Notes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Business Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        <div class="form-text">Internal notes about this business (not visible to customers)</div>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Create Business
                </button>
                <a href="{{ route('businesses.index') }}" class="btn btn-secondary ms-2">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- User Assignment -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Assign Users</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Select users who will have access to this business:</p>
                    
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="user-current" checked disabled>
                            <label class="form-check-label" for="user-current">
                                {{ Auth::user()->name }} (You)
                            </label>
                            <small class="text-muted d-block">You will be automatically assigned as an admin</small>
                        </div>
                        
                        <hr>
                        
                        @if(count($users) > 1)
                            @foreach($users as $user)
                                @if($user->id != Auth::id())
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user-{{ $user->id }}" {{ (is_array(old('users')) && in_array($user->id, old('users'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user-{{ $user->id }}">
                                            {{ $user->name }}
                                            <small class="text-muted d-block">{{ $user->email }}</small>
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> No other users available.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

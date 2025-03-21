@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Customer</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Customer</h5>
        <div>
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Customer
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $customer->full_name) }}" required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="identification_number" class="form-label">Identification Number</label>
                    <input type="text" class="form-control @error('identification_number') is-invalid @enderror" id="identification_number" name="identification_number" value="{{ old('identification_number', $customer->identification_number) }}">
                    @error('identification_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="tax_number" class="form-label">Tax Number</label>
                    <input type="text" class="form-control @error('tax_number') is-invalid @enderror" id="tax_number" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}">
                    @error('tax_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <h6 class="mt-4 mb-3 border-bottom pb-2">Address Information</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="address_line1" class="form-label">Address Line 1</label>
                    <input type="text" class="form-control @error('address_line1') is-invalid @enderror" id="address_line1" name="address_line1" value="{{ old('address_line1', $customer->address_line1) }}">
                    @error('address_line1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="address_line2" class="form-label">Address Line 2</label>
                    <input type="text" class="form-control @error('address_line2') is-invalid @enderror" id="address_line2" name="address_line2" value="{{ old('address_line2', $customer->address_line2) }}">
                    @error('address_line2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $customer->city) }}">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="state" class="form-label">State/Province</label>
                    <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $customer->state) }}">
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="postal_code" class="form-label">Postal Code</label>
                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $customer->country) }}">
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h6 class="mt-4 mb-3 border-bottom pb-2">Additional Information</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $customer->website) }}">
                    @error('website')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="business_id" class="form-label">Business</label>
                    <select class="form-select @error('business_id') is-invalid @enderror" id="business_id" name="business_id">
                        <option value="">-- Select Business --</option>
                        @foreach($businesses as $id => $name)
                            <option value="{{ $id }}" {{ (old('business_id', $customer->business_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('business_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category', $customer->category) }}">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="lead" {{ old('status', $customer->status) == 'lead' ? 'selected' : '' }}>Lead</option>
                        <option value="prospect" {{ old('status', $customer->status) == 'prospect' ? 'selected' : '' }}>Prospect</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="next_contact_date" class="form-label">Next Contact Date</label>
                    <input type="date" class="form-control @error('next_contact_date') is-invalid @enderror" id="next_contact_date" name="next_contact_date" value="{{ old('next_contact_date', $customer->next_contact_date ? $customer->next_contact_date->format('Y-m-d') : '') }}">
                    @error('next_contact_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active Customer
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Customer
                    </button>
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary ms-2">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

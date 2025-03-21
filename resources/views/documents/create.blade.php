@extends('layouts.app')

@section('title', 'Upload Document')
@section('page-title', 'Upload New Document')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('documents.index', ['business_id' => $business->id]) }}">Documents</a></li>
        <li class="breadcrumb-item active" aria-current="page">Upload</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upload Document</h5>
                <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Documents
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                    
                    @if(isset($entityType) && isset($entityId))
                        <input type="hidden" name="documentable_type" value="{{ $entityType }}">
                        <input type="hidden" name="documentable_id" value="{{ $entityId }}">
                        
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i> This document will be associated with 
                            <strong>{{ class_basename($entityType) }} #{{ $entityId }}</strong>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" 
                            id="file" name="file" required>
                        <div class="form-text">Max file size: 10MB. Supported formats: PDF, images, documents, spreadsheets, etc.</div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Document Name (Optional)</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" placeholder="Leave blank to use original filename">
                        <div class="form-text">If left blank, the original filename will be used.</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if(!isset($entityType) || !isset($entityId))
                    <div class="mb-4">
                        <label for="documentable_type" class="form-label">Associate with (Optional)</label>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <select class="form-select @error('documentable_type') is-invalid @enderror" 
                                    id="documentable_type" name="documentable_type">
                                    <option value="">None</option>
                                    <option value="App\Models\Invoice">Invoice</option>
                                    <option value="App\Models\Customer">Customer</option>
                                    <option value="App\Models\Expense">Expense</option>
                                    <option value="App\Models\Service">Service</option>
                                    <option value="App\Models\RecurringBilling">Recurring Billing</option>
                                </select>
                                @error('documentable_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <input type="number" class="form-control @error('documentable_id') is-invalid @enderror" 
                                    id="documentable_id" name="documentable_id" placeholder="ID Number" 
                                    min="1" value="{{ old('documentable_id') }}">
                                @error('documentable_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-text">Optionally associate this document with another record in the system.</div>
                    </div>
                    @endif
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-1"></i> Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Help</h5>
            </div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">About Document Uploads</h6>
                <p>
                    You can upload various types of documents to your business account. These documents can be associated with specific entities like invoices, customers, or expenses.
                </p>
                
                <h6 class="mt-3">Supported File Types</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-file-pdf text-danger me-2"></i> PDF Documents</li>
                    <li><i class="bi bi-file-image text-primary me-2"></i> Images (JPEG, PNG, GIF)</li>
                    <li><i class="bi bi-file-word text-primary me-2"></i> Word Documents</li>
                    <li><i class="bi bi-file-excel text-success me-2"></i> Excel Spreadsheets</li>
                    <li><i class="bi bi-file-text text-dark me-2"></i> Text Files</li>
                    <li><i class="bi bi-file me-2"></i> And more...</li>
                </ul>
                
                <h6 class="mt-3">Organization Tips</h6>
                <ul>
                    <li>Use descriptive names for your documents</li>
                    <li>Associate documents with relevant entities when possible</li>
                    <li>For multiple files, use the Batch Upload option on the Documents page</li>
                </ul>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i> Files are stored securely and are only accessible to users with permission to view your business records.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Simple script to enable/disable the ID field based on entity type selection
    document.addEventListener('DOMContentLoaded', function() {
        const entityTypeSelect = document.getElementById('documentable_type');
        const entityIdInput = document.getElementById('documentable_id');
        
        if (entityTypeSelect && entityIdInput) {
            entityTypeSelect.addEventListener('change', function() {
                if (this.value) {
                    entityIdInput.setAttribute('required', 'required');
                    entityIdInput.removeAttribute('disabled');
                } else {
                    entityIdInput.removeAttribute('required');
                    entityIdInput.setAttribute('disabled', 'disabled');
                    entityIdInput.value = '';
                }
            });
            
            // Initial state
            if (!entityTypeSelect.value) {
                entityIdInput.setAttribute('disabled', 'disabled');
            }
        }
    });
</script>
@endsection

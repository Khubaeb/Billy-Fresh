@extends('layouts.app')

@section('title', 'Documents')
@section('page-title', 'Document Management')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        @if($business)
            <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">Documents</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h5 class="mb-0">Documents</h5>
            @if($business)
                <span class="badge bg-info ms-2">{{ $business->name }}</span>
            @endif
            @if(isset($entity))
                <span class="badge bg-secondary ms-2">
                    {{ class_basename($entityType) }}: {{ $entity->name ?? $entity->id }}
                </span>
            @endif
        </div>
        <div>
            @if($business)
                <a href="{{ route('documents.create', ['business_id' => $business->id]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Upload Document
                </a>
                <button type="button" class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#batchUploadModal">
                    <i class="bi bi-cloud-upload me-1"></i> Batch Upload
                </button>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        @if($businesses->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> You don't have any businesses yet. Create a business first to manage documents.
                <div class="mt-3">
                    <a href="{{ route('businesses.create') }}" class="btn btn-sm btn-primary">Create Business</a>
                </div>
            </div>
        @elseif(!$business)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> Please select a business to view its documents.
            </div>
        @else
            <!-- Business and Entity Selector -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('documents.index') }}" class="mb-3">
                        <div class="input-group">
                            <select class="form-select" name="business_id" onchange="this.form.submit()">
                                <option value="">Select Business</option>
                                @foreach($businesses as $b)
                                    <option value="{{ $b->id }}" {{ $business && $business->id == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
                @if(isset($entityType) && isset($entityId))
                <div class="col-md-6">
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle me-2"></i> Showing documents for 
                        <strong>{{ class_basename($entityType) }} #{{ $entityId }}</strong>
                        <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="ms-2 btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear Filter
                        </a>
                    </div>
                </div>
                @endif
            </div>

            @if($documents->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No documents found for this business.
                    @if(isset($entityType) && isset($entityId))
                        <div class="mt-2">
                            <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Clear Filter
                            </a>
                        </div>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('documents.create', ['business_id' => $business->id]) }}" class="btn btn-sm btn-primary">
                            Upload Document
                        </a>
                    </div>
                </div>
            @else
                <!-- Document Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Related To</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    <td class="text-center">
                                        @if($document->is_image)
                                            <i class="bi bi-file-image text-primary fs-4"></i>
                                        @elseif($document->is_pdf)
                                            <i class="bi bi-file-pdf text-danger fs-4"></i>
                                        @elseif(str_contains($document->type, 'spreadsheet') || str_contains($document->type, 'excel'))
                                            <i class="bi bi-file-excel text-success fs-4"></i>
                                        @elseif(str_contains($document->type, 'document') || str_contains($document->type, 'word'))
                                            <i class="bi bi-file-word text-primary fs-4"></i>
                                        @elseif(str_contains($document->type, 'presentation') || str_contains($document->type, 'powerpoint'))
                                            <i class="bi bi-file-ppt text-warning fs-4"></i>
                                        @elseif(str_contains($document->type, 'text'))
                                            <i class="bi bi-file-text text-info fs-4"></i>
                                        @else
                                            <i class="bi bi-file fs-4"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}" class="text-decoration-none">
                                            {{ $document->name }}
                                        </a>
                                    </td>
                                    <td>{{ $document->type }}</td>
                                    <td>{{ $document->formatted_size }}</td>
                                    <td>
                                        @if($document->documentable_type && $document->documentable_id)
                                            @php
                                                $entityName = class_basename($document->documentable_type);
                                                $entityRoute = strtolower($entityName) . 's.show';
                                            @endphp
                                            <a href="{{ route($entityRoute, $document->documentable_id) }}" class="badge bg-secondary text-decoration-none">
                                                {{ $entityName }} #{{ $document->documentable_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $document->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-success" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteDocumentModal-{{ $document->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteDocumentModal-{{ $document->id }}" tabindex="-1" aria-labelledby="deleteDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteDocumentModalLabel-{{ $document->id }}">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the document <strong>{{ $document->name }}</strong>?</p>
                                                        <p class="text-danger">Warning: This action cannot be undone.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('documents.destroy', $document) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete Document</button>
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
            @endif

            <!-- Batch Upload Modal -->
            <div class="modal fade" id="batchUploadModal" tabindex="-1" aria-labelledby="batchUploadModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="batchUploadModalLabel">Batch Upload Documents</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('documents.batch-upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="business_id" value="{{ $business->id }}">
                                @if(isset($entityType) && isset($entityId))
                                    <input type="hidden" name="documentable_type" value="{{ $entityType }}">
                                    <input type="hidden" name="documentable_id" value="{{ $entityId }}">
                                @endif
                                
                                <div class="mb-3">
                                    <label for="files" class="form-label">Select Multiple Files</label>
                                    <input type="file" class="form-control" id="files" name="files[]" multiple required>
                                    <div class="form-text">You can select multiple files to upload at once. Max 10MB per file.</div>
                                </div>
                                
                                @if(isset($entityType) && isset($entityId))
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> These documents will be associated with 
                                        <strong>{{ class_basename($entityType) }} #{{ $entityId }}</strong>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Upload Files</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

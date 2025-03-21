@extends('layouts.app')

@section('title', $document->name)
@section('page-title', 'Document Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('documents.index', ['business_id' => $business->id]) }}">Documents</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $document->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Document Preview</h5>
                <div>
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center py-4 border rounded mb-4">
                    @if($document->is_image)
                        <img src="{{ asset('storage/' . $document->path) }}" alt="{{ $document->name }}" class="img-fluid rounded" style="max-height: 600px;">
                    @elseif($document->is_pdf)
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ asset('storage/' . $document->path) }}" title="{{ $document->name }}" class="rounded"></iframe>
                        </div>
                    @elseif(str_contains($document->type, 'text/plain') || str_contains($document->type, 'text/html'))
                        <div class="p-3 text-start bg-light rounded" style="max-height: 600px; overflow-y: auto;">
                            <pre>{{ Storage::get($document->path) }}</pre>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            @if(str_contains($document->type, 'spreadsheet') || str_contains($document->type, 'excel'))
                                <i class="bi bi-file-excel text-success" style="font-size: 8rem;"></i>
                            @elseif(str_contains($document->type, 'document') || str_contains($document->type, 'word'))
                                <i class="bi bi-file-word text-primary" style="font-size: 8rem;"></i>
                            @elseif(str_contains($document->type, 'presentation') || str_contains($document->type, 'powerpoint'))
                                <i class="bi bi-file-ppt text-warning" style="font-size: 8rem;"></i>
                            @else
                                <i class="bi bi-file-earmark" style="font-size: 8rem;"></i>
                            @endif
                            <h5 class="mt-3">{{ $document->name }}</h5>
                            <p class="text-muted">This file type cannot be previewed. Click download to view the file.</p>
                            <a href="{{ route('documents.download', $document) }}" class="btn btn-primary mt-3">
                                <i class="bi bi-download me-1"></i> Download File
                            </a>
                        </div>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('documents.index', ['business_id' => $business->id]) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Documents
                    </a>
                    <div>
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteDocumentModal">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Document Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th style="width: 120px;">Name:</th>
                            <td>{{ $document->name }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>{{ $document->type }}</td>
                        </tr>
                        <tr>
                            <th>Size:</th>
                            <td>{{ $document->formatted_size }}</td>
                        </tr>
                        <tr>
                            <th>Business:</th>
                            <td>
                                <a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Uploaded:</th>
                            <td>{{ $document->created_at->format('F j, Y g:i A') }}</td>
                        </tr>
                        @if($document->documentable_type && $document->documentable_id)
                            <tr>
                                <th>Related To:</th>
                                <td>
                                    @php
                                        $entityName = class_basename($document->documentable_type);
                                        $entityRoute = strtolower($entityName) . 's.show';
                                    @endphp
                                    <a href="{{ route($entityRoute, $document->documentable_id) }}" class="badge bg-secondary text-decoration-none">
                                        {{ $entityName }} #{{ $document->documentable_id }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Download File
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDocumentModal">
                        <i class="bi bi-trash me-1"></i> Delete Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteDocumentModal" tabindex="-1" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteDocumentModalLabel">Confirm Delete</h5>
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
@endsection

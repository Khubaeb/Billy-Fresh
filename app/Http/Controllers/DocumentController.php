<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index(Request $request): View
    {
        $businessId = $request->get('business_id');
        $entityType = $request->get('entity_type');
        $entityId = $request->get('entity_id');
        
        // If no business ID provided, get the first business of the user
        if (!$businessId) {
            $firstBusiness = Auth::user()->businesses()->first();
            if ($firstBusiness) {
                $businessId = $firstBusiness->id;
            }
        }
        
        // If we have a business ID, get documents for that business
        if ($businessId) {
            $business = Business::findOrFail($businessId);
            $this->authorize('view', $business);
            
            $query = Document::where('business_id', $businessId)
                ->orderBy('created_at', 'desc');
                
            // If entity type and ID are provided, filter documents for that entity
            if ($entityType && $entityId) {
                $query->where('documentable_type', $entityType)
                    ->where('documentable_id', $entityId);
            }
            
            $documents = $query->get();
        } else {
            $business = null;
            $documents = collect(); // Empty collection
        }
        
        // Get all businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('documents.index', compact('documents', 'business', 'businesses', 'entityType', 'entityId'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(Request $request): View
    {
        $businessId = $request->get('business_id');
        $entityType = $request->get('entity_type');
        $entityId = $request->get('entity_id');
        
        // If no business ID provided, get the first business of the user
        if (!$businessId) {
            $firstBusiness = Auth::user()->businesses()->first();
            if ($firstBusiness) {
                $businessId = $firstBusiness->id;
            } else {
                return redirect()->route('businesses.create')
                    ->with('warning', 'You need to create a business first.');
            }
        }
        
        $business = Business::findOrFail($businessId);
        $this->authorize('update', $business);
        
        // Get businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('documents.create', compact('business', 'businesses', 'entityType', 'entityId'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Business::findOrFail($request->business_id);
        $this->authorize('update', $business);
        
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'documentable_type' => 'nullable|string',
            'documentable_id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
        ]);
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Generate a unique filename
            $filename = time() . '_' . Str::slug($file->getClientOriginalName());
            
            // Store the file
            $path = $file->storeAs('documents/' . $business->id, $filename, 'public');
            
            // If name is not provided, use the original filename
            if (empty($validated['name'])) {
                $validated['name'] = $file->getClientOriginalName();
            }
            
            // Create the document
            $document = new Document([
                'business_id' => $validated['business_id'],
                'documentable_type' => $validated['documentable_type'] ?? null,
                'documentable_id' => $validated['documentable_id'] ?? null,
                'name' => $validated['name'],
                'path' => $path,
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            
            $document->save();
            
            // Determine redirect based on if this is attached to an entity
            if ($document->documentable_type && $document->documentable_id) {
                // Convert namespace format to route name format
                $entityType = strtolower(class_basename($document->documentable_type));
                $redirectRoute = "{$entityType}s.show";
                
                return redirect()->route($redirectRoute, [$document->documentable_id])
                    ->with('success', 'Document uploaded successfully.');
            }
            
            return redirect()->route('documents.index', ['business_id' => $business->id])
                ->with('success', 'Document uploaded successfully.');
        }
        
        return back()->withInput()
            ->with('error', 'No file was uploaded.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): View
    {
        $business = $document->business;
        $this->authorize('view', $business);
        
        return view('documents.show', compact('document', 'business'));
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        $business = $document->business;
        $this->authorize('view', $business);
        
        return Storage::download($document->path, $document->name);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $business = $document->business;
        $this->authorize('delete', $business);
        
        // Store business ID for redirect
        $businessId = $document->business_id;
        
        // Delete the file
        Storage::delete($document->path);
        
        // Delete the document
        $document->delete();
        
        return redirect()->route('documents.index', ['business_id' => $businessId])
            ->with('success', 'Document deleted successfully.');
    }
    
    /**
     * Batch upload multiple documents.
     */
    public function batchUpload(Request $request): RedirectResponse
    {
        $business = Business::findOrFail($request->business_id);
        $this->authorize('update', $business);
        
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'documentable_type' => 'nullable|string',
            'documentable_id' => 'nullable|integer',
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        $uploadedCount = 0;
        
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Generate a unique filename
                $filename = time() . '_' . $uploadedCount . '_' . Str::slug($file->getClientOriginalName());
                
                // Store the file
                $path = $file->storeAs('documents/' . $business->id, $filename, 'public');
                
                // Create the document
                $document = new Document([
                    'business_id' => $validated['business_id'],
                    'documentable_type' => $validated['documentable_type'] ?? null,
                    'documentable_id' => $validated['documentable_id'] ?? null,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
                
                $document->save();
                $uploadedCount++;
            }
            
            // Determine redirect based on if these are attached to an entity
            if (!empty($validated['documentable_type']) && !empty($validated['documentable_id'])) {
                // Convert namespace format to route name format
                $entityType = strtolower(class_basename($validated['documentable_type']));
                $redirectRoute = "{$entityType}s.show";
                
                return redirect()->route($redirectRoute, [$validated['documentable_id']])
                    ->with('success', "{$uploadedCount} documents uploaded successfully.");
            }
            
            return redirect()->route('documents.index', ['business_id' => $business->id])
                ->with('success', "{$uploadedCount} documents uploaded successfully.");
        }
        
        return back()->withInput()
            ->with('error', 'No files were uploaded.');
    }
    
    /**
     * List documents by entity.
     */
    public function listByEntity(Request $request, string $entityType, int $entityId): View
    {
        // First, we need to get the business ID from the entity
        $entityClass = 'App\\Models\\' . Str::studly(Str::singular($entityType));
        $entity = $entityClass::findOrFail($entityId);
        $businessId = $entity->business_id;
        
        $business = Business::findOrFail($businessId);
        $this->authorize('view', $business);
        
        // Get documents for this entity
        $documents = Document::where('documentable_type', $entityClass)
            ->where('documentable_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('documents.index', [
            'documents' => $documents,
            'business' => $business,
            'businesses' => $businesses,
            'entityType' => $entityClass,
            'entityId' => $entityId,
            'entity' => $entity
        ]);
    }
}

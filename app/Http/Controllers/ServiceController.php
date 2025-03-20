<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request): View
    {
        // Handle search query if present
        $search = $request->input('search');
        $status = $request->input('status');
        
        $query = Service::where('user_id', Auth::id());
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if ($status !== null) {
            $isActive = $status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $services = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        
        // Get statistics for the dashboard
        $stats = [
            'total' => Service::where('user_id', Auth::id())->count(),
            'active' => Service::where('user_id', Auth::id())->where('is_active', true)->count(),
            'inactive' => Service::where('user_id', Auth::id())->where('is_active', false)->count(),
        ];
        
        return view('services.index', compact('services', 'stats', 'search', 'status'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(): View
    {
        return view('services.create');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Create service with authenticated user's ID
        $service = new Service();
        $service->user_id = Auth::id();
        $service->name = $validated['name'];
        $service->description = $validated['description'] ?? null;
        $service->sku = $validated['sku'] ?? null;
        $service->price = $validated['price'];
        $service->cost = $validated['cost'] ?? null;
        $service->tax_rate = $validated['tax_rate'] ?? null;
        $service->unit = $validated['unit'] ?? null;
        $service->is_active = $request->has('is_active');
        $service->save();
        
        return redirect()->route('services.show', $service)
            ->with('success', 'Service created successfully');
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service): View
    {
        // Authorization check - only allow viewing service if it belongs to the current user
        $this->authorize('view', $service);
        
        // Get usage statistics for this service
        $invoiceCount = $service->invoiceItems()->count();
        $totalRevenue = $service->invoiceItems()->sum('total');
        
        return view('services.show', compact('service', 'invoiceCount', 'totalRevenue'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service): View
    {
        // Authorization check - only allow editing service if it belongs to the current user
        $this->authorize('update', $service);
        
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        // Authorization check - only allow updating service if it belongs to the current user
        $this->authorize('update', $service);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Update service
        $service->name = $validated['name'];
        $service->description = $validated['description'] ?? null;
        $service->sku = $validated['sku'] ?? null;
        $service->price = $validated['price'];
        $service->cost = $validated['cost'] ?? null;
        $service->tax_rate = $validated['tax_rate'] ?? null;
        $service->unit = $validated['unit'] ?? null;
        $service->is_active = $request->has('is_active');
        $service->save();
        
        return redirect()->route('services.show', $service)
            ->with('success', 'Service updated successfully');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        // Authorization check - only allow deleting service if it belongs to the current user
        $this->authorize('delete', $service);
        
        // Check if this service is used in any invoices
        $usageCount = $service->invoiceItems()->count();
        
        if ($usageCount > 0) {
            return back()->with('error', 'This service cannot be deleted because it is used in ' . $usageCount . ' invoice(s). You can deactivate it instead.');
        }
        
        $service->delete();
        
        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully');
    }
}

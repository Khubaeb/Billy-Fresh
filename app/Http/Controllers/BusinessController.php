<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BusinessController extends Controller
{
    /**
     * Display a listing of the businesses.
     */
    public function index(Request $request): View
    {
        // Get businesses that the user is associated with
        $businesses = Auth::user()->businesses()->with('users')->get();

        return view('businesses.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new business.
     */
    public function create(): View
    {
        // Get all users for assigning to business
        $users = User::all();
        
        return view('businesses.create', compact('users'));
    }

    /**
     * Store a newly created business in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:5120', // 5MB max
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Default currency if not provided
        if (!isset($validated['currency'])) {
            $validated['currency'] = 'USD';
        }

        // Initialize settings if needed
        $validated['settings'] = [
            'invoice_prefix' => $request->input('invoice_prefix', ''),
            'next_invoice_number' => $request->input('next_invoice_number', 1),
            'invoice_terms' => $request->input('invoice_terms', ''),
            'invoice_notes' => $request->input('invoice_notes', ''),
            'invoice_due_days' => $request->input('invoice_due_days', 30),
            'default_tax_rate' => $request->input('default_tax_rate', 0),
            'default_tax_type' => $request->input('default_tax_type', 'percentage'),
        ];

        try {
            // Create the business
            $business = Business::create($validated);

            // Assign users to the business with default role ID 1 (assuming 1 is the default role)
            if (isset($validated['users'])) {
                foreach ($validated['users'] as $userId) {
                    $business->users()->attach($userId, ['role_id' => 1]);
                }
            }

            // Always attach the current user with role 1 (owner/admin)
            if (!isset($validated['users']) || !in_array(Auth::id(), $validated['users'])) {
                $business->users()->attach(Auth::id(), ['role_id' => 1]);
            }

            return redirect()->route('businesses.show', $business)
                ->with('success', 'Business created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create business: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem creating the business: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified business.
     */
    public function show(Business $business): View
    {
        // Authorization check
        $this->authorize('view', $business);
        
        // Load related data
        $business->load(['users', 'customers', 'services', 'invoices']);
        
        // Get some statistics
        $stats = [
            'customer_count' => $business->customers()->count(),
            'service_count' => $business->services()->count(),
            'invoice_count' => $business->invoices()->count(),
            'invoice_total' => $business->invoices()->sum('total_amount'),
            'invoice_paid' => $business->invoices()->where('status', 'paid')->sum('total_amount'),
            'invoice_overdue' => $business->invoices()->where('status', 'overdue')->sum('total_amount'),
        ];
        
        return view('businesses.show', compact('business', 'stats'));
    }

    /**
     * Show the form for editing the specified business.
     */
    public function edit(Business $business): View
    {
        // Authorization check
        $this->authorize('update', $business);
        
        // Get all users for assigning to business
        $users = User::all();
        
        // Get IDs of users already associated with this business
        $businessUserIds = $business->users->pluck('id')->toArray();
        
        return view('businesses.edit', compact('business', 'users', 'businessUserIds'));
    }

    /**
     * Update the specified business in storage.
     */
    public function update(Request $request, Business $business): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $business);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:5120', // 5MB max
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'invoice_prefix' => 'nullable|string|max:20',
            'next_invoice_number' => 'nullable|integer|min:1',
            'invoice_terms' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'invoice_due_days' => 'nullable|integer|min:0',
            'default_tax_rate' => 'nullable|numeric|min:0',
            'default_tax_type' => 'nullable|string|in:percentage,fixed',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Update settings
        $settings = $business->settings ?? [];
        $settingsFields = [
            'invoice_prefix', 'next_invoice_number', 'invoice_terms', 
            'invoice_notes', 'invoice_due_days', 'default_tax_rate', 
            'default_tax_type'
        ];
        
        foreach ($settingsFields as $field) {
            if ($request->has($field)) {
                $settings[$field] = $request->input($field);
            }
        }
        
        $validated['settings'] = $settings;

        try {
            // Update the business
            $business->update($validated);

            // Update user assignments if provided
            if (isset($validated['users'])) {
                // Get current user IDs
                $currentUserIds = $business->users->pluck('id')->toArray();
                
                // Determine users to add and remove
                $usersToAdd = array_diff($validated['users'], $currentUserIds);
                $usersToRemove = array_diff($currentUserIds, $validated['users']);
                
                // Remove users
                foreach ($usersToRemove as $userId) {
                    if ($userId != Auth::id()) {  // Prevent removing current user
                        $business->users()->detach($userId);
                    }
                }
                
                // Add users with role ID 1
                foreach ($usersToAdd as $userId) {
                    $business->users()->attach($userId, ['role_id' => 1]);
                }
            }

            return redirect()->route('businesses.show', $business)
                ->with('success', 'Business updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update business: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem updating the business: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified business from storage.
     */
    public function destroy(Business $business): RedirectResponse
    {
        // Authorization check
        $this->authorize('delete', $business);
        
        try {
            // Check if there are any related records
            $hasRelatedRecords = 
                $business->customers()->exists() || 
                $business->invoices()->exists() || 
                $business->services()->exists() || 
                $business->expenses()->exists() ||
                $business->recurringBillings()->exists();
            
            if ($hasRelatedRecords) {
                return back()->with('error', 'Cannot delete business as it has associated records. To delete, first delete all related customers, invoices, services, etc.');
            }
            
            // Delete logo if exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            
            // Delete the business
            $business->delete();
            
            return redirect()->route('businesses.index')
                ->with('success', 'Business deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete business: ' . $e->getMessage());
            
            return back()->with('error', 'There was a problem deleting the business: ' . $e->getMessage());
        }
    }

    /**
     * Update the settings for a business.
     */
    public function updateSettings(Request $request, Business $business): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $business);
        
        $validated = $request->validate([
            'invoice_prefix' => 'nullable|string|max:20',
            'next_invoice_number' => 'nullable|integer|min:1',
            'invoice_terms' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'invoice_due_days' => 'nullable|integer|min:0',
            'default_tax_rate' => 'nullable|numeric|min:0',
            'default_tax_type' => 'nullable|string|in:percentage,fixed',
            // Add any other settings fields
        ]);
        
        try {
            // Get current settings
            $settings = $business->settings ?? [];
            
            // Update settings with validated data
            foreach ($validated as $key => $value) {
                $settings[$key] = $value;
            }
            
            // Save updated settings
            $business->settings = $settings;
            $business->save();
            
            return redirect()->back()->with('success', 'Business settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update business settings: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem updating the business settings: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(): View
    {
        $customers = Customer::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('full_name')
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $businesses = Business::where('user_id', Auth::id())->pluck('name', 'id');

        return view('customers.create', compact('businesses'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'business_id' => 'nullable|exists:businesses,id',
            'identification_number' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'next_contact_date' => 'nullable|date',
        ]);

        // Set the user ID to the authenticated user
        $validated['user_id'] = Auth::id();
        
        // Set default status if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }
        
        // Set is_active based on status
        $validated['is_active'] = ($validated['status'] === 'active');

        // Create the customer
        $customer = Customer::create($validated);

        if ($customer) {
            return redirect()->route('customers.show', $customer) 
                ->with('success', 'Customer created successfully.');
        } else {
            return back()->withInput()
                ->with('error', 'There was a problem creating the customer.');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        // Authorization check
        $this->authorize('view', $customer);

        // Load related data
        $customer->load('invoices', 'recurringBillings');
        
        // Calculate unpaid amounts
        $unpaidAmount = $customer->getTotalUnpaidAmount();

        return view('customers.show', compact('customer', 'unpaidAmount'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        // Authorization check
        $this->authorize('update', $customer);

        $businesses = Business::where('user_id', Auth::id())->pluck('name', 'id');

        return view('customers.edit', compact('customer', 'businesses'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'business_id' => 'nullable|exists:businesses,id',
            'is_active' => 'boolean',
            'identification_number' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'next_contact_date' => 'nullable|date',
        ]);

        if ($customer->update($validated)) {
            return redirect()->route('customers.show', $customer) 
                ->with('success', 'Customer updated successfully.');
        } else {
            return back()->withInput()
                ->with('error', 'There was a problem updating the customer.');
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse 
    {
        // Authorization check
        $this->authorize('delete', $customer);

        try {
            $customer->delete();
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete customer: ' . $e->getMessage());
            return back()->with('error', 'There was a problem deleting the customer.');
        }
    }
}

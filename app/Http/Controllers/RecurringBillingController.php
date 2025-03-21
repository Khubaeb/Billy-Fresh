<?php

namespace App\Http\Controllers;

use App\Models\RecurringBilling;
use App\Models\Customer;
use App\Models\Business;
use App\Models\Service;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class RecurringBillingController extends Controller
{
    /**
     * Display a listing of recurring billings.
     */
    public function index(Request $request): View
    {
        // Filter parameters
        $status = $request->input('status');
        $customerId = $request->input('customer_id');
        $businessId = $request->input('business_id');
        $search = $request->input('search');
        $frequency = $request->input('frequency');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = RecurringBilling::where('user_id', Auth::id())
            ->with(['customer', 'business', 'service', 'paymentMethod']);
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        if ($frequency) {
            $query->where('frequency', $frequency);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('full_name', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom) {
            $query->whereDate('next_billing_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('next_billing_date', '<=', $dateTo);
        }

        $recurringBillings = $query->orderBy('next_billing_date')->paginate(10);

        // Get data for filtering dropdowns
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();

        // Stats for dashboard cards
        $stats = [
            'total' => $query->count(),
            'active' => $query->where('status', 'active')->where('is_active', true)->count(),
            'paused' => $query->where('status', 'paused')->count(),
            'upcoming' => $query->where('status', 'active')
                              ->where('is_active', true)
                              ->where('next_billing_date', '<=', now()->addDays(7))
                              ->count(),
            'total_monthly' => $query->where('status', 'active')
                                   ->where('is_active', true)
                                   ->where('frequency', 'monthly')
                                   ->sum('amount'),
        ];

        return view('recurring.index', compact('recurringBillings', 'customers', 'businesses', 'stats'));
    }

    /**
     * Show the form for creating a new recurring billing.
     */
    public function create(): View
    {
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('user_id', Auth::id())->orWhere('is_system', true)->orderBy('name')->get();

        return view('recurring.create', compact('customers', 'businesses', 'services', 'paymentMethods'));
    }

    /**
     * Store a newly created recurring billing in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'nullable|exists:services,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'frequency' => 'required|string|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,paused,completed,cancelled',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Set user ID
        $validated['user_id'] = Auth::id();

        // Calculate next billing date
        $startDate = Carbon::parse($validated['start_date']);
        $nextBillingDate = $startDate;

        // If start date is in the past, calculate the next billing date from today
        if ($startDate->isPast()) {
            $nextBillingDate = now();
        }

        // Create a temporary RecurringBilling object to use its calculateNextBillingDate method
        $tempRecurring = new RecurringBilling();
        $tempRecurring->frequency = $validated['frequency'];
        $tempRecurring->interval = $validated['interval'];
        $validated['next_billing_date'] = $tempRecurring->calculateNextBillingDate($nextBillingDate);

        try {
            $recurringBilling = RecurringBilling::create($validated);

            return redirect()->route('recurring.show', $recurringBilling)
                ->with('success', 'Recurring billing created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create recurring billing: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem creating the recurring billing: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified recurring billing.
     */
    public function show(RecurringBilling $recurring): View
    {
        // Authorization check
        $this->authorize('view', $recurring);
        
        // Load relationships
        $recurring->load(['customer', 'business', 'service', 'paymentMethod', 'invoices' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);
        
        return view('recurring.show', compact('recurring'));
    }

    /**
     * Show the form for editing the specified recurring billing.
     */
    public function edit(RecurringBilling $recurring): View
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('user_id', Auth::id())->orWhere('is_system', true)->orderBy('name')->get();
        
        return view('recurring.edit', compact('recurring', 'customers', 'businesses', 'services', 'paymentMethods'));
    }

    /**
     * Update the specified recurring billing in storage.
     */
    public function update(Request $request, RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'nullable|exists:services,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'frequency' => 'required|string|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'next_billing_date' => 'required|date',
            'status' => 'required|string|in:active,paused,completed,cancelled',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $recurring->update($validated);
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', 'Recurring billing updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update recurring billing: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem updating the recurring billing: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified recurring billing from storage.
     */
    public function destroy(RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('delete', $recurring);
        
        try {
            $recurring->delete();
            
            return redirect()->route('recurring.index')
                ->with('success', 'Recurring billing deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete recurring billing: ' . $e->getMessage());
            
            return back()
                ->with('error', 'There was a problem deleting the recurring billing');
        }
    }

    /**
     * Update the status of a recurring billing.
     */
    public function updateStatus(Request $request, RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        $validated = $request->validate([
            'status' => 'required|string|in:active,paused,cancelled',
        ]);
        
        try {
            switch ($validated['status']) {
                case 'active':
                    $recurring->resume();
                    $message = 'Recurring billing activated successfully';
                    break;
                case 'paused':
                    $recurring->pause();
                    $message = 'Recurring billing paused successfully';
                    break;
                case 'cancelled':
                    $recurring->cancel();
                    $message = 'Recurring billing cancelled successfully';
                    break;
            }
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to update recurring billing status: ' . $e->getMessage());
            
            return back()
                ->with('error', 'There was a problem updating the recurring billing status');
        }
    }

    /**
     * Generate an invoice from a recurring billing.
     */
    public function generateInvoice(RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        // Check if the recurring billing is active
        if (!$recurring->isActive()) {
            return back()->with('error', 'Cannot generate invoice for inactive recurring billing');
        }
        
        try {
            // Logic to generate an invoice would go here
            // This would typically create a new invoice based on the recurring billing details
            // For now, we'll just update the billing record
            $recurring->recordBilling();
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', 'Invoice generation process started');
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice: ' . $e->getMessage());
            
            return back()
                ->with('error', 'There was a problem generating the invoice: ' . $e->getMessage());
        }
    }
}

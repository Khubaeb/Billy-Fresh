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
        // Filter by various parameters
        $status = $request->input('status');
        $customerId = $request->input('customer_id');
        $businessId = $request->input('business_id');
        $search = $request->input('search');

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

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $recurringBillings = $query->orderBy('next_billing_date', 'asc')->paginate(10);

        // Get data for filters
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        
        // Get all businesses since there likely won't be too many
        $businesses = Business::orderBy('name')->get();

        // Stats for dashboard cards
        $stats = [
            'total' => $query->count(),
            'active' => $query->where('status', 'active')->where('is_active', true)->count(),
            'due_this_month' => $query->whereDate('next_billing_date', '<=', Carbon::now()->endOfMonth())->count(),
            'total_monthly_revenue' => $query->where('frequency', 'monthly')->sum('amount'),
        ];

        return view('recurring.index', compact('recurringBillings', 'customers', 'businesses', 'stats'));
    }

    /**
     * Show the form for creating a new recurring billing.
     */
    public function create(): View
    {
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $businesses = Business::orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->orderBy('name')->get();
        
        // Get payment methods by business
        $paymentMethods = PaymentMethod::orderBy('type')->get();

        return view('recurring.create', compact('customers', 'businesses', 'services', 'paymentMethods'));
    }

    /**
     * Store a newly created recurring billing in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'nullable|exists:services,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'frequency' => 'required|string|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,paused,completed,cancelled',
            'is_active' => 'boolean',
        ]);

        // Set default values
        $validated['user_id'] = Auth::id();
        $validated['next_billing_date'] = $validated['start_date'];
        $validated['billing_count'] = 0;

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        try {
            $recurringBilling = RecurringBilling::create($validated);
            
            return redirect()->route('recurring.show', $recurringBilling)
                ->with('success', 'Recurring billing created successfully.');
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
        $recurring->load(['customer', 'business', 'service', 'paymentMethod']);
        
        // Calculate next billing dates
        $nextDates = [];
        $startDate = Carbon::parse($recurring->next_billing_date);
        
        for ($i = 0; $i < 12; $i++) {
            switch ($recurring->frequency) {
                case 'daily':
                    $date = $startDate->copy()->addDays($i * $recurring->interval);
                    break;
                case 'weekly':
                    $date = $startDate->copy()->addWeeks($i * $recurring->interval);
                    break;
                case 'monthly':
                    $date = $startDate->copy()->addMonths($i * $recurring->interval);
                    break;
                case 'quarterly':
                    $date = $startDate->copy()->addMonths(3 * $i * $recurring->interval);
                    break;
                case 'yearly':
                    $date = $startDate->copy()->addYears($i * $recurring->interval);
                    break;
                default:
                    $date = $startDate->copy()->addMonths($i * $recurring->interval);
            }
            
            if ($recurring->end_date && $date > Carbon::parse($recurring->end_date)) {
                break;
            }
            
            $nextDates[] = $date->format('Y-m-d');
        }
        
        return view('recurring.show', compact('recurring', 'nextDates'));
    }

    /**
     * Show the form for editing the specified recurring billing.
     */
    public function edit(RecurringBilling $recurring): View
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $businesses = Business::orderBy('name')->get();
        $services = Service::where('user_id', Auth::id())->orderBy('name')->get();
        
        // Get payment methods
        $paymentMethods = PaymentMethod::orderBy('type')->get();
        
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
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'nullable|exists:services,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'frequency' => 'required|string|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'next_billing_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,paused,completed,cancelled',
            'is_active' => 'boolean',
        ]);
        
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }
        
        try {
            $recurring->update($validated);
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', 'Recurring billing updated successfully.');
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
                ->with('success', 'Recurring billing deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete recurring billing: ' . $e->getMessage());
            
            return back()->with('error', 'There was a problem deleting the recurring billing.');
        }
    }
    
    /**
     * Update the status of the specified recurring billing.
     */
    public function updateStatus(Request $request, RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        $validated = $request->validate([
            'status' => 'required|string|in:active,paused,completed,cancelled',
        ]);
        
        try {
            $recurring->update([
                'status' => $validated['status'],
                'is_active' => $validated['status'] === 'active',
            ]);
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', 'Recurring billing status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update recurring billing status: ' . $e->getMessage());
            
            return back()->with('error', 'There was a problem updating the recurring billing status.');
        }
    }
    
    /**
     * Generate an invoice from the recurring billing.
     */
    public function generateInvoice(RecurringBilling $recurring): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $recurring);
        
        try {
            // Code to generate invoice would go here
            // This is a placeholder for now
            
            // Update billing count and last billed date
            $recurring->update([
                'billing_count' => $recurring->billing_count + 1,
                'last_billed_date' => now(),
                'next_billing_date' => $this->calculateNextBillingDate($recurring),
            ]);
            
            return redirect()->route('recurring.show', $recurring)
                ->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice: ' . $e->getMessage());
            
            return back()->with('error', 'There was a problem generating the invoice: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate the next billing date based on frequency and interval.
     */
    private function calculateNextBillingDate(RecurringBilling $recurring): string
    {
        $date = Carbon::parse($recurring->next_billing_date);
        
        switch ($recurring->frequency) {
            case 'daily':
                $date->addDays($recurring->interval);
                break;
            case 'weekly':
                $date->addWeeks($recurring->interval);
                break;
            case 'monthly':
                $date->addMonths($recurring->interval);
                break;
            case 'quarterly':
                $date->addMonths(3 * $recurring->interval);
                break;
            case 'yearly':
                $date->addYears($recurring->interval);
                break;
            default:
                $date->addMonths($recurring->interval);
        }
        
        return $date->format('Y-m-d');
    }
}

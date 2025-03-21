<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request): View
    {
        // Filter by various parameters
        $categoryId = $request->input('category_id');
        $businessId = $request->input('business_id');
        $customerId = $request->input('customer_id');
        $invoiceId = $request->input('invoice_id');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Expense::where('user_id', Auth::id())
            ->with(['category', 'business', 'customer', 'invoice']);

        // Apply filters
        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('expense_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('expense_date', '<=', $dateTo);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(10);

        // Get expense categories for filtering
        $categories = ExpenseCategory::where('user_id', Auth::id())
            ->orWhere('is_system', true)
            ->orderBy('name')
            ->get();

        // Get businesses for filtering
        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();

        // Calculate totals for display
        $totals = [
            'count' => $expenses->total(),
            'amount' => $query->sum('amount'),
            'billable' => $query->where('is_billable', true)->sum('amount'),
            'non_billable' => $query->where('is_billable', false)->sum('amount'),
            'reimbursable' => $query->where('is_reimbursable', true)->sum('amount'),
            'non_reimbursable' => $query->where('is_reimbursable', false)->sum('amount'),
        ];

        return view('expenses.index', compact('expenses', 'categories', 'businesses', 'totals'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        $categories = ExpenseCategory::where('user_id', Auth::id())
            ->orWhere('is_system', true)
            ->orderBy('name')
            ->get();

        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $invoices = Invoice::where('user_id', Auth::id())
            ->whereIn('status', ['draft', 'sent', 'overdue', 'partially_paid'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('expenses.create', compact('categories', 'businesses', 'customers', 'invoices'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'business_id' => 'nullable|exists:businesses,id',
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:255',
            'is_billable' => 'boolean',
            'is_reimbursable' => 'boolean',
            'receipt_image' => 'nullable|image|max:5120', // 5MB max
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
        ]);

        // Set the user ID
        $validated['user_id'] = Auth::id();

        // Set default values if not provided
        if (!isset($validated['is_billable'])) {
            $validated['is_billable'] = false;
        }

        if (!isset($validated['is_reimbursable'])) {
            $validated['is_reimbursable'] = false;
        }

        if (!isset($validated['status'])) {
            $validated['status'] = 'completed';
        }

        if (!isset($validated['currency'])) {
            $validated['currency'] = 'USD';
        }

        // Handle receipt image upload if provided
        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $validated['receipt_path'] = $path;
        }

        try {
            $expense = Expense::create($validated);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create expense: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem creating the expense: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): View
    {
        // Authorization check
        $this->authorize('view', $expense);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        // Authorization check
        $this->authorize('update', $expense);

        $categories = ExpenseCategory::where('user_id', Auth::id())
            ->orWhere('is_system', true)
            ->orderBy('name')
            ->get();

        $businesses = Business::where('user_id', Auth::id())->orderBy('name')->get();
        $customers = Customer::where('user_id', Auth::id())->orderBy('full_name')->get();
        $invoices = Invoice::where('user_id', Auth::id())
            ->whereIn('status', ['draft', 'sent', 'overdue', 'partially_paid'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('expenses.edit', compact('expense', 'categories', 'businesses', 'customers', 'invoices'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'business_id' => 'nullable|exists:businesses,id',
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:255',
            'is_billable' => 'boolean',
            'is_reimbursable' => 'boolean',
            'receipt_image' => 'nullable|image|max:5120', // 5MB max
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
        ]);

        // Handle receipt image upload if provided
        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $validated['receipt_path'] = $path;
        }

        try {
            $expense->update($validated);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update expense: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem updating the expense: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        // Authorization check
        $this->authorize('delete', $expense);
        
        try {
            $expense->delete();
            
            return redirect()->route('expenses.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete expense: ' . $e->getMessage());
            
            return back()->with('error', 'There was a problem deleting the expense.');
        }
    }
}

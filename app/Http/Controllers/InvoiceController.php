<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Business;
use App\Models\PaymentMethod;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     */
    public function index(Request $request): View
    {
        // Filter by status if provided
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Invoice::where('user_id', Auth::id())
            ->with('customer');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->paginate(10);

        // Get statistics for the dashboard
        $stats = [
            'total' => $query->count(),
            'draft' => Invoice::where('user_id', Auth::id())->where('status', 'draft')->count(),
            'sent' => Invoice::where('user_id', Auth::id())->where('status', 'sent')->count(),
            'overdue' => Invoice::where('user_id', Auth::id())->where('status', 'overdue')->count(),
            'paid' => Invoice::where('user_id', Auth::id())->where('status', 'paid')->count(),
            'amount_due' => Invoice::where('user_id', Auth::id())->where('status', '!=', 'paid')->sum('amount_due'),
        ];

        return view('invoices.index', compact('invoices', 'stats', 'status', 'search'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $customers = Customer::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('full_name')
            ->get();

        $services = Service::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        $businesses = Business::where('user_id', Auth::id())->get();
        
        $paymentMethods = PaymentMethod::where('user_id', Auth::id())
            ->orWhere('is_default', true)
            ->get();

        // Generate next invoice number
        $nextInvoiceNumber = $this->generateNextInvoiceNumber();

        return view('invoices.create', compact('customers', 'services', 'businesses', 'paymentMethods', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'nullable|exists:businesses,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'tax_type' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|string|max:20',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
        ]);

        // Set the user ID
        $validated['user_id'] = Auth::id();
        
        // Store the invoice
        DB::beginTransaction();

        try {
            // Create the invoice without items first
            $invoice = Invoice::create($validated);

            // Add items to the invoice
            foreach ($request->items as $itemData) {
                $item = new InvoiceItem($itemData);
                $item->calculateAmounts();
                $invoice->items()->save($item);
            }

            // Calculate invoice totals
            $invoice->calculateTotals()->save();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem creating the invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        // Authorization check
        $this->authorize('view', $invoice);

        // Load relations
        $invoice->load(['customer', 'business', 'items.service', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        // Authorization check
        $this->authorize('update', $invoice);

        $customers = Customer::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('full_name')
            ->get();

        $services = Service::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        $businesses = Business::where('user_id', Auth::id())->get();
        
        $paymentMethods = PaymentMethod::where('user_id', Auth::id())
            ->orWhere('is_default', true)
            ->get();

        return view('invoices.edit', compact('invoice', 'customers', 'services', 'businesses', 'paymentMethods'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'nullable|exists:businesses,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'tax_type' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|string|max:20',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items_to_delete' => 'nullable|array',
            'items_to_delete.*' => 'exists:invoice_items,id',
        ]);

        DB::beginTransaction();

        try {
            // Update invoice
            $invoice->update($validated);

            // Delete items marked for deletion
            if (!empty($request->items_to_delete)) {
                InvoiceItem::whereIn('id', $request->items_to_delete)->delete();
            }

            // Update or create items
            foreach ($request->items as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    $item = InvoiceItem::find($itemData['id']);
                    $item->fill($itemData);
                    $item->calculateAmounts();
                    $item->save();
                } else {
                    // Create new item
                    $item = new InvoiceItem($itemData);
                    $item->calculateAmounts();
                    $invoice->items()->save($item);
                }
            }

            // Recalculate invoice totals
            $invoice->refresh();
            $invoice->calculateTotals()->save();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update invoice: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'There was a problem updating the invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        // Authorization check
        $this->authorize('delete', $invoice);

        try {
            $invoice->delete();
            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice: ' . $e->getMessage());
            return back()->with('error', 'There was a problem deleting the invoice.');
        }
    }

    /**
     * Generate next invoice number.
     *
     * @return string
     */
    private function generateNextInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $latestInvoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestInvoice) {
            $number = (int) substr($latestInvoice->invoice_number, strlen($prefix));
            $number++;
        } else {
            $number = 1001;
        }

        return $prefix . $number;
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $invoice);

        $invoice->status = 'sent';
        $invoice->sent_at = now();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice marked as sent.');
    }

    /**
     * Record payment for an invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->amount_due,
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $paymentData = [
            'user_id' => Auth::id(),
            'payment_date' => $validated['payment_date'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
        ];

        try {
            $payment = $invoice->recordPayment(
                $validated['amount'], 
                $validated['payment_method'],
                $paymentData
            );

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to record payment: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'There was a problem recording the payment.');
        }
    }
}

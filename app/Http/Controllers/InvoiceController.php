<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Business;
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
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
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
            'paid' => Invoice::where('user_id', Auth::id())->where('status', 'paid')->count(),
            'overdue' => Invoice::where('user_id', Auth::id())
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
        ];
        
        return view('invoices.index', compact('invoices', 'stats', 'status'));
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
            ->orderBy('name')
            ->get();
            
        $services = Service::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $businesses = Business::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
            
        // Generate next invoice number
        $latestInvoice = Invoice::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->first();
            
        $nextInvoiceNumber = $latestInvoice 
            ? $this->incrementInvoiceNumber($latestInvoice->invoice_number)
            : 'INV-001';
        
        return view('invoices.create', compact('customers', 'services', 'businesses', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'nullable|exists:businesses,id',
            'invoice_number' => 'required|string|max:50|unique:invoices',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:draft,sent,paid,overdue,partially_paid,cancelled',
            'currency' => 'required|string|size:3',
            'tax_type' => 'nullable|in:percentage,fixed',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        
        // Calculate totals
        $items = $request->input('items');
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;
        
        foreach ($items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemSubtotal;
            
            // Calculate item tax
            if (!empty($item['tax_rate'])) {
                $taxAmount += ($itemSubtotal * $item['tax_rate'] / 100);
            }
            
            // Calculate item discount
            if (!empty($item['discount_rate'])) {
                $discountAmount += ($itemSubtotal * $item['discount_rate'] / 100);
            }
        }
        
        // Calculate invoice level tax if provided
        if ($request->filled('tax_type') && $request->filled('tax_rate')) {
            if ($request->input('tax_type') === 'percentage') {
                $taxAmount += ($subtotal * $request->input('tax_rate') / 100);
            } else {
                $taxAmount += $request->input('tax_rate');
            }
        }
        
        // Calculate invoice level discount if provided
        if ($request->filled('discount_type') && $request->filled('discount_rate')) {
            if ($request->input('discount_type') === 'percentage') {
                $discountAmount += ($subtotal * $request->input('discount_rate') / 100);
            } else {
                $discountAmount += $request->input('discount_rate');
            }
        }
        
        $total = $subtotal + $taxAmount - $discountAmount;
        $amountDue = $total; // Initial amount due is the total
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Create invoice
            $invoice = new Invoice();
            $invoice->user_id = Auth::id();
            $invoice->customer_id = $validated['customer_id'];
            $invoice->business_id = $validated['business_id'] ?? null;
            $invoice->invoice_number = $validated['invoice_number'];
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->reference = $validated['reference'] ?? null;
            $invoice->notes = $validated['notes'] ?? null;
            $invoice->terms = $validated['terms'] ?? null;
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $taxAmount;
            $invoice->discount_amount = $discountAmount;
            $invoice->total = $total;
            $invoice->amount_paid = 0;
            $invoice->amount_due = $amountDue;
            $invoice->status = $validated['status'];
            $invoice->currency = $validated['currency'];
            $invoice->tax_type = $validated['tax_type'] ?? null;
            $invoice->tax_rate = $validated['tax_rate'] ?? null;
            $invoice->discount_type = $validated['discount_type'] ?? null;
            $invoice->discount_rate = $validated['discount_rate'] ?? null;
            
            if ($validated['status'] === 'sent') {
                $invoice->sent_at = now();
            }
            
            if ($validated['status'] === 'paid') {
                $invoice->paid_at = now();
                $invoice->amount_paid = $total;
                $invoice->amount_due = 0;
            }
            
            $invoice->save();
            
            // Create invoice items
            foreach ($items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemTaxAmount = !empty($item['tax_rate']) ? $itemSubtotal * $item['tax_rate'] / 100 : 0;
                $itemDiscountAmount = !empty($item['discount_rate']) ? $itemSubtotal * $item['discount_rate'] / 100 : 0;
                $itemTotal = $itemSubtotal + $itemTaxAmount - $itemDiscountAmount;
                
                $invoice->items()->create([
                    'service_id' => $item['service_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'discount_amount' => $itemDiscountAmount,
                    'total' => $itemTotal,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('invoices.show', $invoice)
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
        
        // Load relationships
        $invoice->load(['customer', 'items', 'payments']);
        
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        // Authorization check
        $this->authorize('update', $invoice);
        
        // Load invoice items
        $invoice->load(['items', 'customer']);
        
        $customers = Customer::where('user_id', Auth::id())
            ->orWhereHas('business', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();
            
        $services = Service::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $businesses = Business::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        
        return view('invoices.edit', compact('invoice', 'customers', 'services', 'businesses'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $invoice);
        
        // Cannot edit invoices that are paid or cancelled
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot edit an invoice that is paid or cancelled.');
        }
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_id' => 'nullable|exists:businesses,id',
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $invoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:draft,sent,paid,overdue,partially_paid,cancelled',
            'currency' => 'required|string|size:3',
            'tax_type' => 'nullable|in:percentage,fixed',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        
        // Calculate totals
        $items = $request->input('items');
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;
        
        foreach ($items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemSubtotal;
            
            // Calculate item tax
            if (!empty($item['tax_rate'])) {
                $taxAmount += ($itemSubtotal * $item['tax_rate'] / 100);
            }
            
            // Calculate item discount
            if (!empty($item['discount_rate'])) {
                $discountAmount += ($itemSubtotal * $item['discount_rate'] / 100);
            }
        }
        
        // Calculate invoice level tax if provided
        if ($request->filled('tax_type') && $request->filled('tax_rate')) {
            if ($request->input('tax_type') === 'percentage') {
                $taxAmount += ($subtotal * $request->input('tax_rate') / 100);
            } else {
                $taxAmount += $request->input('tax_rate');
            }
        }
        
        // Calculate invoice level discount if provided
        if ($request->filled('discount_type') && $request->filled('discount_rate')) {
            if ($request->input('discount_type') === 'percentage') {
                $discountAmount += ($subtotal * $request->input('discount_rate') / 100);
            } else {
                $discountAmount += $request->input('discount_rate');
            }
        }
        
        $total = $subtotal + $taxAmount - $discountAmount;
        $amountDue = $total - $invoice->amount_paid;
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Update invoice
            $invoice->customer_id = $validated['customer_id'];
            $invoice->business_id = $validated['business_id'] ?? null;
            $invoice->invoice_number = $validated['invoice_number'];
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->reference = $validated['reference'] ?? null;
            $invoice->notes = $validated['notes'] ?? null;
            $invoice->terms = $validated['terms'] ?? null;
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $taxAmount;
            $invoice->discount_amount = $discountAmount;
            $invoice->total = $total;
            $invoice->amount_due = $amountDue;
            
            // Update status
            $oldStatus = $invoice->status;
            $newStatus = $validated['status'];
            
            if ($oldStatus !== $newStatus) {
                if ($newStatus === 'sent' && !$invoice->sent_at) {
                    $invoice->sent_at = now();
                }
                
                if ($newStatus === 'paid' && !$invoice->paid_at) {
                    $invoice->paid_at = now();
                    $invoice->amount_paid = $total;
                    $invoice->amount_due = 0;
                }
                
                $invoice->status = $newStatus;
            }
            
            $invoice->currency = $validated['currency'];
            $invoice->tax_type = $validated['tax_type'] ?? null;
            $invoice->tax_rate = $validated['tax_rate'] ?? null;
            $invoice->discount_type = $validated['discount_type'] ?? null;
            $invoice->discount_rate = $validated['discount_rate'] ?? null;
            
            $invoice->save();
            
            // Handle invoice items
            $existingItemIds = $invoice->items->pluck('id')->toArray();
            $updatedItemIds = [];
            
            foreach ($items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemTaxAmount = !empty($item['tax_rate']) ? $itemSubtotal * $item['tax_rate'] / 100 : 0;
                $itemDiscountAmount = !empty($item['discount_rate']) ? $itemSubtotal * $item['discount_rate'] / 100 : 0;
                $itemTotal = $itemSubtotal + $itemTaxAmount - $itemDiscountAmount;
                
                // Update existing item or create new one
                if (!empty($item['id'])) {
                    $invoiceItem = $invoice->items()->find($item['id']);
                    if ($invoiceItem) {
                        $invoiceItem->update([
                            'service_id' => $item['service_id'] ?? null,
                            'name' => $item['name'],
                            'description' => $item['description'] ?? null,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'tax_rate' => $item['tax_rate'] ?? 0,
                            'discount_rate' => $item['discount_rate'] ?? 0,
                            'subtotal' => $itemSubtotal,
                            'tax_amount' => $itemTaxAmount,
                            'discount_amount' => $itemDiscountAmount,
                            'total' => $itemTotal,
                        ]);
                        
                        $updatedItemIds[] = $invoiceItem->id;
                    }
                } else {
                    $invoiceItem = $invoice->items()->create([
                        'service_id' => $item['service_id'] ?? null,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'discount_rate' => $item['discount_rate'] ?? 0,
                        'subtotal' => $itemSubtotal,
                        'tax_amount' => $itemTaxAmount,
                        'discount_amount' => $itemDiscountAmount,
                        'total' => $itemTotal,
                    ]);
                    
                    $updatedItemIds[] = $invoiceItem->id;
                }
            }
            
            // Delete items that were removed
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                $invoice->items()->whereIn('id', $itemsToDelete)->delete();
            }
            
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
        
        // Cannot delete invoices that are paid
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot delete a paid invoice.');
        }
        
        try {
            // Delete invoice items and the invoice
            $invoice->items()->delete();
            $invoice->delete();
            
            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice: ' . $e->getMessage());
            
            return back()
                ->with('error', 'There was a problem deleting the invoice.');
        }
    }
    
    /**
     * Increment the invoice number
     */
    private function incrementInvoiceNumber(string $invoiceNumber): string
    {
        // Extract the numeric part
        preg_match('/(\D*)(\d+)$/', $invoiceNumber, $matches);
        
        if (count($matches) >= 3) {
            $prefix = $matches[1];
            $number = (int) $matches[2];
            $nextNumber = $number + 1;
            
            // Format the number with leading zeros based on current length
            $digits = strlen($matches[2]);
            $formatted = str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
            
            return $prefix . $formatted;
        }
        
        // If no pattern found, just append -1 to the invoice number
        return $invoiceNumber . '-1';
    }
}

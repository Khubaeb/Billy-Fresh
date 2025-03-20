@extends('layouts.app')

@section('title', 'Create Invoice')
@section('page-title', 'Create Invoice')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Invoice</li>
    </ol>
</nav>
@endsection

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $nextInvoiceNumber) }}" required>
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} {{ $customer->company ? '(' . $customer->company . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="business_id" class="form-label">From Business</label>
                            <select class="form-select @error('business_id') is-invalid @enderror" id="business_id" name="business_id">
                                <option value="">Personal</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('business_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}" placeholder="Purchase order or reference number">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width: 250px;">Item</th>
                                    <th style="min-width: 100px;">Quantity</th>
                                    <th style="min-width: 150px;">Unit Price</th>
                                    <th style="min-width: 150px;">Subtotal</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                <!-- Item rows will be added here dynamically -->
                                <tr class="item-row">
                                    <td>
                                        <input type="text" class="form-control" name="items[0][name]" placeholder="Item name or description" required>
                                        <textarea class="form-control mt-2" name="items[0][description]" rows="2" placeholder="Additional details (optional)"></textarea>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="items[0][quantity]" value="1" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text currency-symbol">$</span>
                                            <input type="number" class="form-control item-price" name="items[0][unit_price]" value="0.00" min="0" step="0.01" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text currency-symbol">$</span>
                                            <input type="text" class="form-control item-subtotal" value="0.00" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Notes & Terms -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Customer Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Notes visible to customer">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="terms" class="form-label">Terms & Conditions</label>
                            <textarea class="form-control @error('terms') is-invalid @enderror" id="terms" name="terms" rows="3" placeholder="Invoice terms and conditions">{{ old('terms', 'Payment is due within 30 days from the date of invoice.') }}</textarea>
                            @error('terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Invoice Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr>
                    
                    <!-- Discount -->
                    <div class="mb-3">
                        <label class="form-label">Discount</label>
                        <div class="input-group">
                            <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type" style="max-width: 110px;">
                                <option value="">No Discount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                            <input type="number" class="form-control @error('discount_rate') is-invalid @enderror" id="discount_rate" name="discount_rate" value="{{ old('discount_rate', 0) }}" min="0" step="0.01">
                            <span class="input-group-text" id="discount_symbol">%</span>
                        </div>
                        @error('discount_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Tax -->
                    <div class="mb-3">
                        <label class="form-label">Tax</label>
                        <div class="input-group">
                            <select class="form-select @error('tax_type') is-invalid @enderror" id="tax_type" name="tax_type" style="max-width: 110px;">
                                <option value="">No Tax</option>
                                <option value="percentage" {{ old('tax_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ old('tax_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                            <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', 0) }}" min="0" step="0.01">
                            <span class="input-group-text" id="tax_symbol">%</span>
                        </div>
                        @error('tax_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr>
                    
                    <!-- Totals -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="currency-symbol">$</span>
                            <span id="subtotal">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span class="currency-symbol">$</span>
                            <span id="discount">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span class="currency-symbol">$</span>
                            <span id="tax">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span class="currency-symbol">$</span>
                            <span id="total">0.00</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-check2-circle me-1"></i> Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemRows = document.getElementById('itemRows');
        const addItemBtn = document.getElementById('addItemBtn');
        const currencySelect = document.getElementById('currency');
        const discountType = document.getElementById('discount_type');
        const discountRate = document.getElementById('discount_rate');
        const discountSymbol = document.getElementById('discount_symbol');
        const taxType = document.getElementById('tax_type');
        const taxRate = document.getElementById('tax_rate');
        const taxSymbol = document.getElementById('tax_symbol');
        
        // Currency symbols for different currencies
        const currencySymbols = {
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'CAD': 'CA$',
            'AUD': 'A$'
        };
        
        // Update currency symbols when currency changes
        function updateCurrencySymbols() {
            const symbol = currencySymbols[currencySelect.value] || '$';
            document.querySelectorAll('.currency-symbol').forEach(el => {
                el.textContent = symbol;
            });
        }
        
        // Initialize currency symbols
        updateCurrencySymbols();
        
        // Add item row
        addItemBtn.addEventListener('click', function() {
            const rowCount = document.querySelectorAll('.item-row').length;
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="items[${rowCount}][name]" placeholder="Item name or description" required>
                    <textarea class="form-control mt-2" name="items[${rowCount}][description]" rows="2" placeholder="Additional details (optional)"></textarea>
                </td>
                <td>
                    <input type="number" class="form-control item-quantity" name="items[${rowCount}][quantity]" value="1" min="0.01" step="0.01" required>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text currency-symbol">${currencySymbols[currencySelect.value] || '$'}</span>
                        <input type="number" class="form-control item-price" name="items[${rowCount}][unit_price]" value="0.00" min="0" step="0.01" required>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text currency-symbol">${currencySymbols[currencySelect.value] || '$'}</span>
                        <input type="text" class="form-control item-subtotal" value="0.00" readonly>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            itemRows.appendChild(newRow);
            
            // Add event listeners to the new row
            const quantityInput = newRow.querySelector('.item-quantity');
            const priceInput = newRow.querySelector('.item-price');
            const removeBtn = newRow.querySelector('.remove-item');
            
            quantityInput.addEventListener('input', calculateTotals);
            priceInput.addEventListener('input', calculateTotals);
            removeBtn.addEventListener('click', function() {
                newRow.remove();
                calculateTotals();
                renumberItems();
            });
        });
        
        // Remove item row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                const button = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
                const row = button.closest('.item-row');
                
                // Don't remove if it's the only row
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateTotals();
                    renumberItems();
                }
            }
        });
        
        // Calculate line item subtotals and invoice totals
        function calculateTotals() {
            let subtotal = 0;
            
            // Calculate each line item
            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const lineSubtotal = quantity * price;
                
                row.querySelector('.item-subtotal').value = lineSubtotal.toFixed(2);
                subtotal += lineSubtotal;
            });
            
            // Calculate discount
            let discountAmount = 0;
            if (discountType.value === 'percentage') {
                discountAmount = subtotal * (parseFloat(discountRate.value) || 0) / 100;
            } else if (discountType.value === 'fixed') {
                discountAmount = parseFloat(discountRate.value) || 0;
            }
            
            // Calculate tax
            let taxAmount = 0;
            if (taxType.value === 'percentage') {
                taxAmount = subtotal * (parseFloat(taxRate.value) || 0) / 100;
            } else if (taxType.value === 'fixed') {
                taxAmount = parseFloat(taxRate.value) || 0;
            }
            
            // Calculate total
            const total = subtotal + taxAmount - discountAmount;
            
            // Update summary
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('discount').textContent = discountAmount.toFixed(2);
            document.getElementById('tax').textContent = taxAmount.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
        }
        
        // Renumber item inputs after deletion
        function renumberItems() {
            document.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelectorAll('input, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                    }
                });
            });
        }
        
        // Update discount symbol based on type
        discountType.addEventListener('change', function() {
            discountSymbol.textContent = this.value === 'percentage' ? '%' : currencySymbols[currencySelect.value] || '$';
            calculateTotals();
        });
        
        // Update tax symbol based on type
        taxType.addEventListener('change', function() {
            taxSymbol.textContent = this.value === 'percentage' ? '%' : currencySymbols[currencySelect.value] || '$';
            calculateTotals();
        });
        
        // Update all totals when discount or tax rate changes
        discountRate.addEventListener('input', calculateTotals);
        taxRate.addEventListener('input', calculateTotals);
        
        // Update currency symbols when currency changes
        currencySelect.addEventListener('change', function() {
            updateCurrencySymbols();
            
            // Also update discount and tax symbols if they're fixed
            if (discountType.value === 'fixed') {
                discountSymbol.textContent = currencySymbols[this.value] || '$';
            }
            
            if (taxType.value === 'fixed') {
                taxSymbol.textContent = currencySymbols[this.value] || '$';
            }
        });
        
        // Add event listeners to initial row
        document.querySelectorAll('.item-quantity, .item-price').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
        
        // Initial calculation
        calculateTotals();
    });
</script>
@endpush

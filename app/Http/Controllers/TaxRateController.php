<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the tax rates.
     */
    public function index(Request $request): View
    {
        $businessId = $request->get('business_id');
        
        // If no business ID provided, get the first business of the user
        if (!$businessId) {
            $firstBusiness = Auth::user()->businesses()->first();
            if ($firstBusiness) {
                $businessId = $firstBusiness->id;
            }
        }
        
        // If we have a business ID, get tax rates for that business
        if ($businessId) {
            $business = Business::findOrFail($businessId);
            $this->authorize('view', $business); // Check if user can view the business
            
            $taxRates = TaxRate::where('business_id', $businessId)
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();
        } else {
            $business = null;
            $taxRates = collect(); // Empty collection
        }
        
        // Get all businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('tax-rates.index', compact('taxRates', 'business', 'businesses'));
    }

    /**
     * Show the form for creating a new tax rate.
     */
    public function create(Request $request): View
    {
        $businessId = $request->get('business_id');
        
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
        $this->authorize('update', $business); // Check if user can update the business
        
        // Get businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('tax-rates.create', compact('business', 'businesses'));
    }

    /**
     * Store a newly created tax rate in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Business::findOrFail($request->business_id);
        $this->authorize('update', $business); // Check if user can update the business
        
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
        ]);
        
        // If this is marked as default, remove default from other tax rates
        if ($request->has('is_default') && $request->is_default) {
            TaxRate::where('business_id', $business->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $taxRate = TaxRate::create($validated);
        
        return redirect()->route('tax-rates.index', ['business_id' => $business->id])
            ->with('success', 'Tax rate created successfully.');
    }

    /**
     * Display the specified tax rate.
     */
    public function show(TaxRate $taxRate): View
    {
        $business = $taxRate->business;
        $this->authorize('view', $business); // Check if user can view the business
        
        // Get a count of invoices and services using this tax rate
        $invoiceCount = $taxRate->invoices()->count();
        $serviceCount = $taxRate->services()->count();
        
        return view('tax-rates.show', compact('taxRate', 'business', 'invoiceCount', 'serviceCount'));
    }

    /**
     * Show the form for editing the specified tax rate.
     */
    public function edit(TaxRate $taxRate): View
    {
        $business = $taxRate->business;
        $this->authorize('update', $business); // Check if user can update the business
        
        // Get businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        return view('tax-rates.edit', compact('taxRate', 'business', 'businesses'));
    }

    /**
     * Update the specified tax rate in storage.
     */
    public function update(Request $request, TaxRate $taxRate): RedirectResponse
    {
        $business = $taxRate->business;
        $this->authorize('update', $business); // Check if user can update the business
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
        ]);
        
        // If this is marked as default, remove default from other tax rates
        if ($request->has('is_default') && $request->is_default) {
            TaxRate::where('business_id', $business->id)
                ->where('id', '!=', $taxRate->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $taxRate->update($validated);
        
        return redirect()->route('tax-rates.index', ['business_id' => $business->id])
            ->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Remove the specified tax rate from storage.
     */
    public function destroy(TaxRate $taxRate): RedirectResponse
    {
        $business = $taxRate->business;
        $this->authorize('delete', $business); // Check if user can delete from the business
        
        // Check if this tax rate is being used by any invoices or services
        $invoiceCount = $taxRate->invoices()->count();
        $serviceCount = $taxRate->services()->count();
        
        if ($invoiceCount > 0 || $serviceCount > 0) {
            return back()->with('error', 
                "Cannot delete this tax rate. It's currently used by {$invoiceCount} invoices and {$serviceCount} services.");
        }
        
        // Check if this is the last tax rate for the business
        $taxRateCount = TaxRate::where('business_id', $business->id)->count();
        if ($taxRateCount <= 1) {
            return back()->with('error', 
                "Cannot delete the last tax rate for a business. Each business must have at least one tax rate.");
        }
        
        // If this is the default tax rate, make another one default
        if ($taxRate->is_default) {
            $newDefault = TaxRate::where('business_id', $business->id)
                ->where('id', '!=', $taxRate->id)
                ->first();
                
            if ($newDefault) {
                $newDefault->is_default = true;
                $newDefault->save();
            }
        }
        
        $taxRate->delete();
        
        return redirect()->route('tax-rates.index', ['business_id' => $business->id])
            ->with('success', 'Tax rate deleted successfully.');
    }

    /**
     * Set this tax rate as the default for its business.
     */
    public function setDefault(TaxRate $taxRate): RedirectResponse
    {
        $business = $taxRate->business;
        $this->authorize('update', $business); // Check if user can update the business
        
        // Remove default from other tax rates for this business
        TaxRate::where('business_id', $business->id)
            ->where('id', '!=', $taxRate->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
        
        // Set this tax rate as default
        $taxRate->is_default = true;
        $taxRate->save();
        
        return redirect()->route('tax-rates.index', ['business_id' => $business->id])
            ->with('success', 'Default tax rate updated successfully.');
    }
}

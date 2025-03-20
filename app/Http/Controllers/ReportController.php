<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Display the income report.
     */
    public function income(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;
        
        // Set date range based on period
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->input('start_date')) 
                    : Carbon::now()->subMonth();
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->input('end_date')) 
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }
        
        // Get income data for the period
        $invoices = Invoice::where('user_id', Auth::id())
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();
        
        // Calculate totals
        $totalInvoiced = $invoices->sum('total');
        $totalPaid = $invoices->sum('amount_paid');
        $totalOutstanding = $invoices->sum('amount_due');
        
        // Group by date for chart
        $incomeByDate = $invoices
            ->groupBy(function ($invoice) {
                return Carbon::parse($invoice->invoice_date)->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'invoiced' => $group->sum('total'),
                    'paid' => $group->sum('amount_paid'),
                ];
            });
        
        return view('reports.income', compact(
            'invoices', 
            'totalInvoiced', 
            'totalPaid', 
            'totalOutstanding', 
            'incomeByDate',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the expense report.
     */
    public function expenses(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;
        
        // Set date range based on period (same logic as income method)
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->input('start_date')) 
                    : Carbon::now()->subMonth();
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->input('end_date')) 
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }
        
        // Get expense data for the period
        $expenses = Expense::where('user_id', Auth::id())
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();
        
        // Calculate totals
        $totalExpenses = $expenses->sum('amount');
        $totalTaxes = $expenses->sum('tax_amount');
        
        // Group by category for chart
        $expensesByCategory = $expenses
            ->groupBy('category')
            ->map(function ($group) {
                return $group->sum('amount');
            });
        
        return view('reports.expenses', compact(
            'expenses', 
            'totalExpenses', 
            'totalTaxes', 
            'expensesByCategory',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the customer report.
     */
    public function customers(Request $request): View
    {
        $period = $request->input('period', 'year');
        $startDate = null;
        $endDate = null;
        
        // Set date range based on period
        switch ($period) {
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->input('start_date')) 
                    : Carbon::now()->subYear();
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->input('end_date')) 
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
        }
        
        // Get customer data with invoice information
        $customers = Customer::where('user_id', Auth::id())
            ->withCount('invoices')
            ->withSum(['invoices' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'total')
            ->withSum(['invoices' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'amount_paid')
            ->orderByDesc('invoices_total')
            ->take(10)
            ->get();
        
        return view('reports.customers', compact(
            'customers',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the tax report.
     */
    public function tax(Request $request): View
    {
        $period = $request->input('period', 'quarter');
        $startDate = null;
        $endDate = null;
        
        // Set date range based on period
        switch ($period) {
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->input('start_date')) 
                    : Carbon::now()->subQuarter();
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->input('end_date')) 
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
        }
        
        // Get tax data
        $collectedTax = Invoice::where('user_id', Auth::id())
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('tax_amount');
        
        $paidTax = Expense::where('user_id', Auth::id())
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('tax_amount');
        
        $netTax = $collectedTax - $paidTax;
        
        // Monthly breakdown
        $monthlyTaxData = [];
        $currentDate = clone $startDate;
        
        while ($currentDate->lte($endDate)) {
            $monthStart = clone $currentDate->startOfMonth();
            $monthEnd = clone $currentDate->endOfMonth();
            
            $monthlyCollected = Invoice::where('user_id', Auth::id())
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->sum('tax_amount');
            
            $monthlyPaid = Expense::where('user_id', Auth::id())
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('tax_amount');
            
            $monthlyTaxData[$currentDate->format('M Y')] = [
                'collected' => $monthlyCollected,
                'paid' => $monthlyPaid,
                'net' => $monthlyCollected - $monthlyPaid
            ];
            
            $currentDate->addMonth();
        }
        
        return view('reports.tax', compact(
            'collectedTax',
            'paidTax',
            'netTax',
            'monthlyTaxData',
            'period',
            'startDate',
            'endDate'
        ));
    }
}

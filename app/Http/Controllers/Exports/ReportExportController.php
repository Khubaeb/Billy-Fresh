<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportExportController extends Controller
{
    /**
     * Get date range based on period
     */
    private function getDateRange(Request $request)
    {
        $period = $request->input('period', 'month');
        $now = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date', $now->copy()->subMonth()->format('Y-m-d')));
                $endDate = Carbon::parse($request->input('end_date', $now->format('Y-m-d')));
                break;
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }
        
        return [$startDate, $endDate, $period];
    }

    /**
     * Generate income report data
     */
    private function generateIncomeReportData(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getDateRange($request);

        // Get invoices for the period
        $invoices = Invoice::whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('customer')
            ->get();

        // Calculate totals
        $totalInvoiced = $invoices->sum('total');
        $totalPaid = $invoices->sum('amount_paid');
        $totalOutstanding = $totalInvoiced - $totalPaid;

        // Generate daily income data for chart
        $incomeByDate = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayInvoices = $invoices->filter(function($invoice) use ($dateKey) {
                return $invoice->invoice_date->format('Y-m-d') === $dateKey;
            });
            
            $incomeByDate[$dateKey] = [
                'invoiced' => $dayInvoices->sum('total'),
                'paid' => $dayInvoices->sum('amount_paid')
            ];
            
            $currentDate->addDay();
        }

        return [
            'invoices' => $invoices,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'incomeByDate' => $incomeByDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period
        ];
    }

    /**
     * Generate expenses report data
     */
    private function generateExpensesReportData(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getDateRange($request);

        // Get expenses for the period
        $expenses = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Calculate totals
        $totalExpenses = $expenses->sum('amount');
        $totalTaxes = $expenses->sum('tax_amount');

        // Group expenses by category
        $expensesByCategory = $expenses->groupBy('category')
            ->map(function ($items) {
                return $items->sum('amount');
            })
            ->toArray();

        return [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'totalTaxes' => $totalTaxes,
            'expensesByCategory' => $expensesByCategory,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period
        ];
    }

    /**
     * Generate tax report data
     */
    private function generateTaxReportData(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getDateRange($request);

        // Get invoices and expenses for the period
        $invoices = Invoice::whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        $expenses = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Calculate tax amounts
        $collectedTax = $invoices->sum('tax_amount');
        $paidTax = $expenses->sum('tax_amount');
        $netTax = $collectedTax - $paidTax;

        // Generate monthly tax data
        $monthlyTaxData = [];
        $currentDate = $startDate->copy()->startOfMonth();
        $endDateMonth = $endDate->copy()->startOfMonth();
        
        while ($currentDate->lte($endDateMonth)) {
            $monthKey = $currentDate->format('M Y');
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $monthInvoices = $invoices->filter(function($invoice) use ($monthStart, $monthEnd) {
                return $invoice->invoice_date->between($monthStart, $monthEnd);
            });
            
            $monthExpenses = $expenses->filter(function($expense) use ($monthStart, $monthEnd) {
                return $expense->expense_date->between($monthStart, $monthEnd);
            });
            
            $collected = $monthInvoices->sum('tax_amount');
            $paid = $monthExpenses->sum('tax_amount');
            
            $monthlyTaxData[$monthKey] = [
                'collected' => $collected,
                'paid' => $paid,
                'net' => $collected - $paid
            ];
            
            $currentDate->addMonth();
        }

        return [
            'collectedTax' => $collectedTax,
            'paidTax' => $paidTax,
            'netTax' => $netTax,
            'monthlyTaxData' => $monthlyTaxData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period
        ];
    }

    /**
     * Generate customers report data
     */
    private function generateCustomersReportData(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getDateRange($request);

        // Get customers with their invoice data for the period
        $customers = Customer::withCount(['invoices' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }])
            ->withSum(['invoices' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }], 'total')
            ->withSum(['invoices' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }], 'amount_paid')
            ->orderByDesc('invoices_total')
            ->get();

        return [
            'customers' => $customers,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period
        ];
    }

    /**
     * Export income report as PDF
     */
    public function incomeAsPdf(Request $request)
    {
        $data = $this->generateIncomeReportData($request);
        
        $pdf = PDF::loadView('exports.income_pdf', $data);
        
        return $pdf->download('income-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export expenses report as PDF
     */
    public function expensesAsPdf(Request $request)
    {
        $data = $this->generateExpensesReportData($request);
        
        $pdf = PDF::loadView('exports.expenses_pdf', $data);
        
        return $pdf->download('expenses-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export tax report as PDF
     */
    public function taxAsPdf(Request $request)
    {
        $data = $this->generateTaxReportData($request);
        
        $pdf = PDF::loadView('exports.tax_pdf', $data);
        
        return $pdf->download('tax-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export customers report as PDF
     */
    public function customersAsPdf(Request $request)
    {
        $data = $this->generateCustomersReportData($request);
        
        $pdf = PDF::loadView('exports.customers_pdf', $data);
        
        return $pdf->download('customers-report-' . now()->format('Y-m-d') . '.pdf');
    }
}

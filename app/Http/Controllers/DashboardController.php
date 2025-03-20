<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\RecurringBilling;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // In a real application we would fetch these from the database
        // For now we'll use placeholder data as shown in the dashboard view

        // You can uncomment and use these queries when the models are implemented
        /*
        $stats = [
            'total_revenue' => Invoice::whereIn('status', ['paid', 'partially_paid'])->sum('total_amount'),
            'outstanding' => Invoice::whereIn('status', ['pending', 'overdue'])->sum('total_amount'),
            'expenses' => Expense::sum('amount'),
            'net_income' => Invoice::whereIn('status', ['paid', 'partially_paid'])->sum('total_amount') - Expense::sum('amount')
        ];

        $recentInvoices = Invoice::with('customer')->orderBy('created_at', 'desc')->limit(4)->get();
        $recentExpenses = Expense::with('category')->orderBy('created_at', 'desc')->limit(4)->get();
        $upcomingPayments = RecurringBilling::with('customer')->where('next_billing_date', '>=', now())
            ->where('next_billing_date', '<=', now()->addDays(14))
            ->orderBy('next_billing_date')
            ->limit(3)
            ->get();
        */

        // Return view with placeholder data
        return view('dashboard.index');
    }
}

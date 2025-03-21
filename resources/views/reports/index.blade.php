@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Financial Reports')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Reports</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-graph-up-arrow text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Income Report</h5>
                <p class="card-text text-muted">Track your revenue, view invoice payments, and analyze income trends.</p>
                <a href="{{ route('reports.income') }}" class="btn btn-outline-success mt-3">
                    View Income Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-cash-stack text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Expense Report</h5>
                <p class="card-text text-muted">Monitor your expenses by category, vendor, and time period.</p>
                <a href="{{ route('reports.expenses') }}" class="btn btn-outline-danger mt-3">
                    View Expense Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Customer Report</h5>
                <p class="card-text text-muted">Analyze customer spending, payments, and outstanding balances.</p>
                <a href="{{ route('reports.customers') }}" class="btn btn-outline-primary mt-3">
                    View Customer Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-percent text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Tax Report</h5>
                <p class="card-text text-muted">Calculate collected and paid taxes for tax filing and planning.</p>
                <a href="{{ route('reports.tax') }}" class="btn btn-outline-warning mt-3">
                    View Tax Report
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Financial Overview</h5>
                <div>
                    <select class="form-select form-select-sm" id="overviewPeriod">
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year" selected>This Year</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-3">Income vs. Expenses</h6>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="incomeExpenseChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-3">Summary</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th scope="row">Total Income</th>
                                        <td class="text-end text-success">$<span id="totalIncome">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Total Expenses</th>
                                        <td class="text-end text-danger">$<span id="totalExpenses">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Net Profit</th>
                                        <td class="text-end fw-bold">$<span id="netProfit">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Total Tax Collected</th>
                                        <td class="text-end">$<span id="taxCollected">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Total Tax Paid</th>
                                        <td class="text-end">$<span id="taxPaid">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Net Tax</th>
                                        <td class="text-end">$<span id="netTax">0.00</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Top Customers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Invoices</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="topCustomersTable">
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Loading customer data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="{{ route('reports.customers') }}" class="btn btn-sm btn-outline-primary mt-2">View Full Report</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Expense Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="expensePieChart"></canvas>
                </div>
                <div class="text-end">
                    <a href="{{ route('reports.expenses') }}" class="btn btn-sm btn-outline-danger mt-2">View Full Report</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Invoices</h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentInvoicesTable">
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Loading invoice data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Placeholder data - in a real implementation, this would be retrieved from the server via AJAX
        const incomeData = [12500, 14000, 10500, 15000, 13000, 16500, 17500, 16000, 18000, 15500, 20000, 22000];
        const expenseData = [8000, 9500, 7500, 10000, 11000, 9000, 9500, 10500, 12000, 11500, 12500, 14000];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Calculate totals
        const totalIncome = incomeData.reduce((sum, val) => sum + val, 0);
        const totalExpenses = expenseData.reduce((sum, val) => sum + val, 0);
        const netProfit = totalIncome - totalExpenses;
        
        // Update summary figures
        document.getElementById('totalIncome').textContent = totalIncome.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('totalExpenses').textContent = totalExpenses.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('netProfit').textContent = netProfit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('taxCollected').textContent = (totalIncome * 0.08).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('taxPaid').textContent = (totalExpenses * 0.05).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('netTax').textContent = ((totalIncome * 0.08) - (totalExpenses * 0.05)).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        // Income vs. Expense Chart
        const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: expenseData,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Expense Pie Chart
        const expenseCtx = document.getElementById('expensePieChart').getContext('2d');
        const expenseChart = new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: ['Office Supplies', 'Rent', 'Utilities', 'Marketing', 'Travel', 'Other'],
                datasets: [{
                    data: [15, 30, 10, 20, 15, 10],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(23, 162, 184, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Populate Top Customers
        const topCustomersTable = document.getElementById('topCustomersTable');
        const customers = [
            { name: 'Acme Corporation', invoices: 12, amount: 28500 },
            { name: 'Smith Enterprises', invoices: 8, amount: 15200 },
            { name: 'Johnson LLC', invoices: 5, amount: 12750 },
            { name: 'Global Solutions Ltd', invoices: 7, amount: 10450 },
            { name: 'Tech Innovations Inc', invoices: 4, amount: 8900 }
        ];
        
        topCustomersTable.innerHTML = '';
        customers.forEach(customer => {
            topCustomersTable.innerHTML += `
                <tr>
                    <td>${customer.name}</td>
                    <td>${customer.invoices}</td>
                    <td class="text-end">$${customer.amount.toLocaleString()}</td>
                </tr>
            `;
        });
        
        // Populate Recent Invoices
        const recentInvoicesTable = document.getElementById('recentInvoicesTable');
        const invoices = [
            { id: 'INV-1045', customer: 'Acme Corporation', date: '2025-03-15', amount: 3500, status: 'Paid' },
            { id: 'INV-1044', customer: 'Smith Enterprises', date: '2025-03-10', amount: 2200, status: 'Paid' },
            { id: 'INV-1043', customer: 'Tech Innovations Inc', date: '2025-03-05', amount: 1750, status: 'Pending' },
            { id: 'INV-1042', customer: 'Global Solutions Ltd', date: '2025-02-28', amount: 4250, status: 'Overdue' },
            { id: 'INV-1041', customer: 'Johnson LLC', date: '2025-02-25', amount: 5800, status: 'Paid' }
        ];
        
        recentInvoicesTable.innerHTML = '';
        invoices.forEach(invoice => {
            let statusClass = 'bg-secondary';
            if (invoice.status === 'Paid') statusClass = 'bg-success';
            if (invoice.status === 'Pending') statusClass = 'bg-warning';
            if (invoice.status === 'Overdue') statusClass = 'bg-danger';
            
            recentInvoicesTable.innerHTML += `
                <tr>
                    <td>${invoice.id}</td>
                    <td>${invoice.customer}</td>
                    <td>${new Date(invoice.date).toLocaleDateString()}</td>
                    <td>$${invoice.amount.toLocaleString()}</td>
                    <td><span class="badge ${statusClass}">${invoice.status}</span></td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
            `;
        });
        
        // Period selector change event
        document.getElementById('overviewPeriod').addEventListener('change', function() {
            // In a real implementation, this would fetch new data based on the selected period
            alert('In a live implementation, this would refresh the data for the selected period: ' + this.value);
        });
    });
</script>
@endsection

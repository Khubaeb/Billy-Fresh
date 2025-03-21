@extends('layouts.app')

@section('title', 'Expense Report')
@section('page-title', 'Expense Report')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active" aria-current="page">Expense Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Expense Report</h5>
                <div class="d-flex">
                    <form method="GET" action="{{ route('reports.expenses') }}" class="d-flex me-2">
                        <select class="form-select form-select-sm" name="period" onchange="this.form.submit()">
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </form>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="{{ route('exports.expenses.pdf', request()->query()) }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#" onclick="window.print(); return false;"><i class="bi bi-printer me-2"></i>Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($period == 'custom')
                <div class="mb-4">
                    <form method="GET" action="{{ route('reports.expenses') }}" class="row g-3">
                        <input type="hidden" name="period" value="custom">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Date Range</button>
                        </div>
                    </form>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Expenses</h6>
                                <h3 class="mb-0 text-danger">${{ number_format($totalExpenses, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tax Amount</h6>
                                <h3 class="mb-0">${{ number_format($totalTaxes, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ round(($totalTaxes / max($totalExpenses, 0.01)) * 100, 2) }}% of total expenses
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <h6 class="mb-3">Monthly Expenses</h6>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="expenseMonthlyChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-3">Expense by Category</h6>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="expensePieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Expenses ({{ count($expenses) }})</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Vendor</th>
                                    <th>Description</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Tax</th>
                                    <th>Billable</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->category }}</td>
                                    <td>{{ $expense->vendor }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td>{{ $expense->reference_number }}</td>
                                    <td class="text-end">${{ number_format($expense->amount, 2) }}</td>
                                    <td class="text-end">${{ number_format($expense->tax_amount, 2) }}</td>
                                    <td>
                                        @if($expense->is_billable)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No expenses found for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="5">Total</td>
                                    <td class="text-end">${{ number_format($totalExpenses, 2) }}</td>
                                    <td class="text-end">${{ number_format($totalTaxes, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
        // Expenses by category chart data
        const expensesByCategory = @json($expensesByCategory);
        const categories = Object.keys(expensesByCategory);
        const categoryAmounts = categories.map(category => expensesByCategory[category]);
        
        // Generate colors for each category
        const categoryColors = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
            'rgba(83, 102, 255, 0.7)',
            'rgba(40, 159, 64, 0.7)',
            'rgba(210, 206, 86, 0.7)'
        ];
        
        // Monthly data - this is a placeholder in a real app this would be generated by the controller
        // For simplicity, we're creating dummy data here
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyData = [
            4500, 3800, 5100, 4900, 5600, 6200, 5800, 5400, 6100, 5700, 6500, 7200
        ];
        
        // Expense Pie Chart
        const expensePieCtx = document.getElementById('expensePieChart').getContext('2d');
        new Chart(expensePieCtx, {
            type: 'doughnut',
            data: {
                labels: categories,
                datasets: [{
                    data: categoryAmounts,
                    backgroundColor: categoryColors.slice(0, categories.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${context.label}: $${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Monthly Expense Chart
        const expenseMonthlyCtx = document.getElementById('expenseMonthlyChart').getContext('2d');
        new Chart(expenseMonthlyCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: monthlyData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
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
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Expenses: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection

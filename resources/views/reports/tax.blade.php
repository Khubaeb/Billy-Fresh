@extends('layouts.app')

@section('title', 'Tax Report')
@section('page-title', 'Tax Report')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tax Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tax Report</h5>
                <div class="d-flex">
                    <form method="GET" action="{{ route('reports.tax') }}" class="d-flex me-2">
                        <select class="form-select form-select-sm" name="period" onchange="this.form.submit()">
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
                            <li><a class="dropdown-item" href="{{ route('exports.tax.pdf', request()->query()) }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#" onclick="window.print(); return false;"><i class="bi bi-printer me-2"></i>Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($period == 'custom')
                <div class="mb-4">
                    <form method="GET" action="{{ route('reports.tax') }}" class="row g-3">
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
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tax Collected</h6>
                                <h3 class="mb-0 text-primary">${{ number_format($collectedTax, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    From sales and invoices
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tax Paid</h6>
                                <h3 class="mb-0 text-danger">${{ number_format($paidTax, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    From expenses and purchases
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Net Tax {{ $netTax >= 0 ? 'Owed' : 'Credit' }}</h6>
                                <h3 class="mb-0 {{ $netTax >= 0 ? 'text-warning' : 'text-success' }}">${{ number_format(abs($netTax), 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Monthly Tax Summary</h6>
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="taxChart"></canvas>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Monthly Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Tax Collected</th>
                                    <th class="text-end">Tax Paid</th>
                                    <th class="text-end">Net Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyTaxData as $month => $data)
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td class="text-end text-primary">${{ number_format($data['collected'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($data['paid'], 2) }}</td>
                                    <td class="text-end {{ $data['net'] >= 0 ? 'text-warning' : 'text-success' }}">
                                        ${{ number_format(abs($data['net']), 2) }}
                                        <small class="text-muted">{{ $data['net'] >= 0 ? '(Owed)' : '(Credit)' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No tax data found for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td>Total</td>
                                    <td class="text-end text-primary">${{ number_format($collectedTax, 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($paidTax, 2) }}</td>
                                    <td class="text-end {{ $netTax >= 0 ? 'text-warning' : 'text-success' }}">
                                        ${{ number_format(abs($netTax), 2) }}
                                        <small class="text-muted">{{ $netTax >= 0 ? '(Owed)' : '(Credit)' }}</small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Tax Notes</h6>
                            </div>
                            <div class="card-body">
                                <p>This tax report provides a summary of sales tax collected from customers and tax paid on expenses. Use this report to help prepare your tax returns and track your tax liabilities.</p>
                                
                                <h6 class="mt-3">Important Points:</h6>
                                <ul>
                                    <li>This report is for informational purposes only and should not be considered tax advice.</li>
                                    <li>Always consult with a qualified tax professional about your specific tax situation.</li>
                                    <li>If you collect sales tax, make sure to remit it to the appropriate tax authorities by the required deadlines.</li>
                                    <li>Keep detailed records of all tax-related transactions for audit purposes.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Tax Rate Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tax Rate</th>
                                                <th>Description</th>
                                                <th class="text-end">Rate</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- This would be populated with actual tax rates in a real implementation -->
                                            <tr>
                                                <td>Standard</td>
                                                <td>General sales tax</td>
                                                <td class="text-end">8.25%</td>
                                                <td class="text-end">${{ number_format($collectedTax * 0.8, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Reduced</td>
                                                <td>For essential goods</td>
                                                <td class="text-end">2.50%</td>
                                                <td class="text-end">${{ number_format($collectedTax * 0.2, 2) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="3">Total</th>
                                                <th class="text-end">${{ number_format($collectedTax, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Tax Filing Due:</strong> April 15, 2025
                                </div>
                            </div>
                        </div>
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
        // Tax chart data
        const monthlyTaxData = @json($monthlyTaxData);
        const months = Object.keys(monthlyTaxData);
        const collectedData = months.map(month => monthlyTaxData[month].collected);
        const paidData = months.map(month => monthlyTaxData[month].paid);
        const netData = months.map(month => monthlyTaxData[month].net);
        
        // Tax Chart
        const ctx = document.getElementById('taxChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Tax Collected',
                        data: collectedData,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Tax Paid',
                        data: paidData,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Net Tax',
                        data: netData,
                        type: 'line',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4
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
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection

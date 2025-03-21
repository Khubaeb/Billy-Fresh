@extends('layouts.app')

@section('title', 'Income Report')
@section('page-title', 'Income Report')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active" aria-current="page">Income Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Income Report</h5>
                <div class="d-flex">
                    <form method="GET" action="{{ route('reports.income') }}" class="d-flex me-2">
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
                            <li><a class="dropdown-item" href="{{ route('exports.income.pdf', request()->query()) }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#" onclick="window.print(); return false;"><i class="bi bi-printer me-2"></i>Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($period == 'custom')
                <div class="mb-4">
                    <form method="GET" action="{{ route('reports.income') }}" class="row g-3">
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
                                <h6 class="text-muted mb-2">Total Invoiced</h6>
                                <h3 class="mb-0">${{ number_format($totalInvoiced, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Paid</h6>
                                <h3 class="mb-0 text-success">${{ number_format($totalPaid, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ round(($totalPaid / max($totalInvoiced, 0.01)) * 100) }}% of invoiced amount
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Outstanding</h6>
                                <h3 class="mb-0 text-danger">${{ number_format($totalOutstanding, 2) }}</h3>
                                <p class="text-muted small mb-0">
                                    {{ round(($totalOutstanding / max($totalInvoiced, 0.01)) * 100) }}% of invoiced amount
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Income Trend</h6>
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Invoices ({{ count($invoices) }})</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                                    <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end text-success">${{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end {{ $invoice->amount_due > 0 ? 'text-danger' : '' }}">${{ number_format($invoice->amount_due, 2) }}</td>
                                    <td>
                                        @if($invoice->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @elseif($invoice->status == 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No invoices found for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4">Total</td>
                                    <td class="text-end">${{ number_format($totalInvoiced, 2) }}</td>
                                    <td class="text-end text-success">${{ number_format($totalPaid, 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($totalOutstanding, 2) }}</td>
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
        // Income chart data
        const incomeData = @json($incomeByDate);
        const dates = Object.keys(incomeData);
        const invoicedData = dates.map(date => incomeData[date].invoiced);
        const paidData = dates.map(date => incomeData[date].paid);
        
        // Format dates for display
        const formattedDates = dates.map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        // Income chart
        const ctx = document.getElementById('incomeChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedDates,
                datasets: [
                    {
                        label: 'Invoiced',
                        data: invoicedData,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Paid',
                        data: paidData,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
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

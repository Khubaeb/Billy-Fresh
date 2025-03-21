@extends('layouts.app')

@section('title', 'Customer Report')
@section('page-title', 'Customer Report')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active" aria-current="page">Customer Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Report</h5>
                <div class="d-flex">
                    <form method="GET" action="{{ route('reports.customers') }}" class="d-flex me-2">
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
                            <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-printer me-2"></i>Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($period == 'custom')
                <div class="mb-4">
                    <form method="GET" action="{{ route('reports.customers') }}" class="row g-3">
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

                <div class="mb-4">
                    <h6 class="mb-3">Top Customers by Revenue</h6>
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="customerRevenueChart"></canvas>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Customer Activity ({{ count($customers) }})</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Paid Amount</th>
                                    <th class="text-end">Outstanding</th>
                                    <th class="text-center">Payment Rate</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <span class="text-secondary">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                                            </div>
                                            <div>
                                                {{ $customer->name }}
                                                @if($customer->company_name)
                                                    <br><small class="text-muted">{{ $customer->company_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $customer->email }}<br>
                                        <small class="text-muted">{{ $customer->phone }}</small>
                                    </td>
                                    <td class="text-center">{{ $customer->invoices_count }}</td>
                                    <td class="text-end">${{ number_format($customer->invoices_total, 2) }}</td>
                                    <td class="text-end text-success">${{ number_format($customer->invoices_amount_paid, 2) }}</td>
                                    <td class="text-end {{ ($customer->invoices_total - $customer->invoices_amount_paid) > 0 ? 'text-danger' : '' }}">
                                        ${{ number_format(max(0, $customer->invoices_total - $customer->invoices_amount_paid), 2) }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $paymentRate = $customer->invoices_total > 0 ? 
                                                round(($customer->invoices_amount_paid / $customer->invoices_total) * 100) : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $paymentRate >= 80 ? 'bg-success' : ($paymentRate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                role="progressbar" style="width: {{ $paymentRate }}%;" 
                                                aria-valuenow="{{ $paymentRate }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $paymentRate }}%</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No customer data found for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Customer Acquisition</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height:250px;">
                                    <canvas id="customerAcquisitionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Customer Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height:250px;">
                                    <canvas id="customerRetentionChart"></canvas>
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
        // Top customers by revenue
        const customerLabels = [];
        const customerData = [];
        const customerColors = [
            'rgba(0, 123, 255, 0.7)',
            'rgba(40, 167, 69, 0.7)',
            'rgba(255, 193, 7, 0.7)',
            'rgba(220, 53, 69, 0.7)',
            'rgba(111, 66, 193, 0.7)',
            'rgba(23, 162, 184, 0.7)',
            'rgba(108, 117, 125, 0.7)',
            'rgba(0, 123, 255, 0.5)',
            'rgba(40, 167, 69, 0.5)',
            'rgba(255, 193, 7, 0.5)'
        ];
        
        // Extract customer data for chart
        @foreach($customers as $index => $customer)
            @if($index < 10) // Limit to top 10 for readability
                customerLabels.push("{{ $customer->name }}");
                customerData.push({{ $customer->invoices_total ?? 0 }});
            @endif
        @endforeach
        
        // Customer Revenue Chart
        const revenueCtx = document.getElementById('customerRevenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: customerLabels,
                datasets: [{
                    label: 'Revenue',
                    data: customerData,
                    backgroundColor: customerColors,
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
                                return 'Revenue: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Example data for acquisition chart - in a real app this would come from the backend
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const acquisitionData = [5, 7, 4, 9, 6, 8, 12, 9, 11, 8, 10, 14];
        
        // Customer Acquisition Chart
        const acquisitionCtx = document.getElementById('customerAcquisitionChart').getContext('2d');
        new Chart(acquisitionCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'New Customers',
                    data: acquisitionData,
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Example data for retention chart - in a real app this would come from the backend
        const retentionLabels = ['1+ Year', '6-12 Months', '3-6 Months', '1-3 Months', 'New (<1 Month)'];
        const retentionData = [35, 25, 15, 15, 10];
        const retentionColors = [
            'rgba(40, 167, 69, 0.7)',
            'rgba(0, 123, 255, 0.7)',
            'rgba(255, 193, 7, 0.7)',
            'rgba(108, 117, 125, 0.7)',
            'rgba(220, 53, 69, 0.7)'
        ];
        
        // Customer Retention Chart
        const retentionCtx = document.getElementById('customerRetentionChart').getContext('2d');
        new Chart(retentionCtx, {
            type: 'doughnut',
            data: {
                labels: retentionLabels,
                datasets: [{
                    data: retentionData,
                    backgroundColor: retentionColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${percentage}%`;
                            }
                        }
                    }
                }
            }
        });

        // Set up export actions (in a real implementation these would trigger actual exports)
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                alert('In a live implementation, this would export the report as ' + this.textContent.trim());
            });
        });
    });
</script>
@endsection

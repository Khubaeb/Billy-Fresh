@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
    </ol>
</nav>
@endsection

@section('content')
<!-- Quick Actions -->
<div class="row mb-4 quick-actions">
    <div class="col-12">
        <h5 class="mb-3">Quick Actions</h5>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('invoices.create') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-receipt"></i>
                <div>New Invoice</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('customers.create') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-person-plus"></i>
                <div>New Customer</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('expenses.create') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-cash"></i>
                <div>New Expense</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('services.create') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-box"></i>
                <div>New Service</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('recurring.create') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-arrow-repeat"></i>
                <div>New Recurring</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="{{ route('reports.index') }}" class="text-decoration-none">
            <div class="quick-action-btn">
                <i class="bi bi-bar-chart"></i>
                <div>Reports</div>
            </div>
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Financial Overview</h5>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <h6 class="card-title">Total Revenue</h6>
            <div class="card-value">${{ number_format(12500, 2) }}</div>
            <div class="text-success small mt-2">
                <i class="bi bi-arrow-up"></i> 12% from last month
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="icon">
                <i class="bi bi-credit-card"></i>
            </div>
            <h6 class="card-title">Outstanding</h6>
            <div class="card-value">${{ number_format(3200, 2) }}</div>
            <div class="text-danger small mt-2">
                <i class="bi bi-arrow-up"></i> 5% from last month
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <h6 class="card-title">Expenses</h6>
            <div class="card-value">${{ number_format(4800, 2) }}</div>
            <div class="text-danger small mt-2">
                <i class="bi bi-arrow-up"></i> 8% from last month
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <h6 class="card-title">Net Income</h6>
            <div class="card-value">${{ number_format(7700, 2) }}</div>
            <div class="text-success small mt-2">
                <i class="bi bi-arrow-up"></i> 15% from last month
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Invoices</h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Number</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Smith</td>
                                <td>INV-001</td>
                                <td>{{ now()->subDays(2)->format('m/d/Y') }}</td>
                                <td>${{ number_format(1200, 2) }}</td>
                                <td><span class="badge bg-success">Paid</span></td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>INV-002</td>
                                <td>{{ now()->subDays(5)->format('m/d/Y') }}</td>
                                <td>${{ number_format(850, 2) }}</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                            </tr>
                            <tr>
                                <td>Michael Brown</td>
                                <td>INV-003</td>
                                <td>{{ now()->subDays(8)->format('m/d/Y') }}</td>
                                <td>${{ number_format(1750, 2) }}</td>
                                <td><span class="badge bg-danger">Overdue</span></td>
                            </tr>
                            <tr>
                                <td>Emily Davis</td>
                                <td>INV-004</td>
                                <td>{{ now()->subDays(10)->format('m/d/Y') }}</td>
                                <td>${{ number_format(950, 2) }}</td>
                                <td><span class="badge bg-success">Paid</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Expenses</h5>
                <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Office Supplies</td>
                                <td>Printer Ink</td>
                                <td>{{ now()->subDays(3)->format('m/d/Y') }}</td>
                                <td>${{ number_format(125, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Rent</td>
                                <td>Office Space</td>
                                <td>{{ now()->subDays(7)->format('m/d/Y') }}</td>
                                <td>${{ number_format(1500, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Utilities</td>
                                <td>Electricity Bill</td>
                                <td>{{ now()->subDays(9)->format('m/d/Y') }}</td>
                                <td>${{ number_format(210, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Software</td>
                                <td>Accounting Software</td>
                                <td>{{ now()->subDays(12)->format('m/d/Y') }}</td>
                                <td>${{ number_format(79, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Payments -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upcoming Payments</h5>
                <a href="{{ route('recurring.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Next Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Smith</td>
                                <td>Website Maintenance</td>
                                <td>${{ number_format(150, 2) }}</td>
                                <td>{{ now()->addDays(3)->format('m/d/Y') }}</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>SEO Services</td>
                                <td>${{ number_format(300, 2) }}</td>
                                <td>{{ now()->addDays(5)->format('m/d/Y') }}</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Michael Brown</td>
                                <td>Cloud Hosting</td>
                                <td>${{ number_format(99, 2) }}</td>
                                <td>{{ now()->addDays(7)->format('m/d/Y') }}</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-receipt"></i>
                                    </a>
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

@push('scripts')
<script>
    // This is a placeholder for future dashboard chart initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Chart initialization will go here
    });
</script>
@endpush

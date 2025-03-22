@extends('layouts.app')

@section('title', 'Accounting Portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Accounting Portal</h1>
            
            <!-- Accounting Office Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">The business's accounting office</h2>
                    <div class="text-end">
                        <div class="text-primary">
                            <a href="{{ route('accounting.settings') }}">editing</a>
                        </div>
                        <div>
                            office
                            <strong>{{ $accountingSettings->accounting_office_name ?? 'Not set' }}</strong>,
                            {{ $accountingSettings->accounting_contact_number ?? 'No contact number' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- For Business Accounting Use -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">For business accounting use</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('accounting.options') }}" class="btn btn-outline-primary w-100">
                                More options
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('accounting.download-materials') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    Download accounting materials
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports from accounting for business use -->
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Reports from accounting for business use</h2>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                        <div class="col">
                            <a href="{{ route('accounting.account-card') }}" class="btn btn-outline-secondary w-100 h-100 py-3">
                                Account card
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('accounting.advanced-payments') }}" class="btn btn-outline-secondary w-100 h-100 py-3">
                                Payments (advance payments)
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('accounting.vat-payments') }}" class="btn btn-outline-secondary w-100 h-100 py-3">
                                VAT payments
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('accounting.profit-loss') }}" class="btn btn-outline-secondary w-100 h-100 py-3">
                                Profit and loss
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('accounting.centralized-card') }}" class="btn btn-outline-secondary w-100 h-100 py-3">
                                Centralized card printing
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income Statement Preview Section -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4">
                        <i class="bi bi-dot"></i> Income Statement January-February 2025
                    </h2>
                    <a href="{{ route('accounting.income-statement') }}" class="text-decoration-none">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Presentation</label>
                                <select class="form-select">
                                    <option>Presentation</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Months</label>
                                <select class="form-select">
                                    <option>January-February</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <select class="form-select">
                                    <option>2025</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Period</label>
                                <select class="form-select">
                                    <option>Bimonthly</option>
                                </select>
                            </div>
                        </div>

                        <!-- Revenue Section -->
                        <h3 class="h5 my-4">Revenue</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">02/2025</th>
                                        <th class="text-end">01/2025</th>
                                        <th class="text-end">Revenue documents by document date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Revenue</td>
                                        <td class="text-end">0</td>
                                        <td class="text-end">0</td>
                                        <td class="text-end">0</td>
                                        <td class="text-end">Revenue</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Expense Files Section -->
                        <h3 class="h5 my-4">Expense files</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">02/2025</th>
                                        <th class="text-end">01/2025</th>
                                        <th class="text-end">Uploaded files by upload date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href="#" class="btn btn-link">Download</a>
                                        </td>
                                        <td class="text-end">2</td>
                                        <td class="text-end">2</td>
                                        <td class="text-end">0</td>
                                        <td class="text-end">amount</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Details Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div></div>
                            <button class="btn btn-link text-dark dropdown-toggle" type="button">
                                Detail <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

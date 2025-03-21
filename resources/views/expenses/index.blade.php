@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Expenses</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Expense Summary</h5>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Expense
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Expenses</h6>
                        <h3 class="mb-0">{{ number_format($totals['amount'], 2) }}</h3>
                        <div class="small mt-2">{{ $totals['count'] }} entries</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="text-white-50">Billable Expenses</h6>
                        <h3 class="mb-0">{{ number_format($totals['billable'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="text-dark-50">Reimbursable Expenses</h6>
                        <h3 class="mb-0">{{ number_format($totals['reimbursable'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Expense List</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <form action="{{ route('expenses.index') }}" method="GET" class="bg-light p-3 rounded">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search...">
                        </div>
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="business_id" class="form-label">Business</label>
                            <select class="form-select" id="business_id" name="business_id">
                                <option value="">All Businesses</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ request('business_id') == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <div class="d-grid gap-2">
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($expenses->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> No expenses found. Get started by adding your first expense.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('expenses.show', $expense) }}" class="text-decoration-none">
                                        {{ $expense->description }}
                                    </a>
                                    @if($expense->is_billable)
                                        <span class="badge bg-success ms-1">Billable</span>
                                    @endif
                                    @if($expense->is_reimbursable)
                                        <span class="badge bg-warning text-dark ms-1">Reimbursable</span>
                                    @endif
                                </td>
                                <td>{{ $expense->category->name ?? 'N/A' }}</td>
                                <td>{{ $expense->vendor_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $expense->status == 'completed' ? 'success' : ($expense->status == 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </td>
                                <td class="text-end">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteExpenseModal-{{ $expense->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteExpenseModal-{{ $expense->id }}" tabindex="-1" aria-labelledby="deleteExpenseModalLabel-{{ $expense->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteExpenseModalLabel-{{ $expense->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the expense "{{ $expense->description }}"? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

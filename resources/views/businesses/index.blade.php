@extends('layouts.app')

@section('title', 'Businesses')
@section('page-title', 'Business Management')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Businesses</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Businesses</h5>
        <a href="{{ route('businesses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Business
        </a>
    </div>
    <div class="card-body">
        @if($businesses->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> You don't have any businesses yet. Create a new business to get started.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Contact</th>
                            <th>Tax Info</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($businesses as $business)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($business->logo_path)
                                            <div class="me-3">
                                                <img src="{{ asset('storage/' . $business->logo_path) }}" alt="{{ $business->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: contain;">
                                            </div>
                                        @else
                                            <div class="me-3">
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-building fs-4 text-secondary"></i>
                                                </div>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('businesses.show', $business) }}" class="text-decoration-none">
                                                <h6 class="mb-0">{{ $business->name }}</h6>
                                            </a>
                                            @if($business->business_id)
                                                <small class="text-muted">ID: {{ $business->business_id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($business->email)
                                        <div><i class="bi bi-envelope me-1"></i> {{ $business->email }}</div>
                                    @endif
                                    @if($business->phone)
                                        <div><i class="bi bi-telephone me-1"></i> {{ $business->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($business->tax_number)
                                        <div>Tax: {{ $business->tax_number }}</div>
                                    @endif
                                    @if($business->registration_number)
                                        <div>Reg: {{ $business->registration_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        @foreach($business->users->take(3) as $user)
                                            <div class="avatar-circle me-1" data-bs-toggle="tooltip" title="{{ $user->name }}">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                        @if($business->users->count() > 3)
                                            <div class="avatar-circle bg-secondary me-1" data-bs-toggle="tooltip" title="{{ $business->users->count() - 3 }} more">
                                                +{{ $business->users->count() - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($business->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('businesses.show', $business) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('businesses.edit', $business) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteBusinessModal-{{ $business->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteBusinessModal-{{ $business->id }}" tabindex="-1" aria-labelledby="deleteBusinessModalLabel-{{ $business->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteBusinessModalLabel-{{ $business->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete <strong>{{ $business->name }}</strong>?</p>
                                                    <p class="text-danger">Warning: This action is irreversible and will remove all business data.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('businesses.destroy', $business) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Business</button>
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
        @endif
    </div>
</div>
@endsection

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    background-color: #007bff;
    border-radius: 50%;
    color: white;
    text-align: center;
    line-height: 35px;
    font-weight: 600;
    font-size: 14px;
}
</style>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection

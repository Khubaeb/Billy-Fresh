@extends('layouts.app')

@section('title', 'No Business Found')
@section('page-title', 'No Business Found')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Business Settings</h5>
                <a href="{{ route('settings.user') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-gear me-1"></i> User Settings
                </a>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-building-x text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4>No Business Found</h4>
                    <p class="text-muted mb-4">
                        You don't have any businesses set up yet. Create a business to access business settings.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('businesses.create') }}" class="btn btn-primary">
                            <i class="bi bi-building-add me-1"></i> Create a Business
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Available Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-person-gear text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>User Settings</h5>
                                <p class="text-muted mb-3">Configure your personal preferences and security settings.</p>
                                <a href="{{ route('settings.user') }}" class="btn btn-outline-primary">
                                    User Settings
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-person-badge text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>Profile</h5>
                                <p class="text-muted mb-3">Update your personal information and profile details.</p>
                                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                @can('manageSystem', \App\Models\User::class)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-sliders text-warning" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>System Settings</h5>
                                <p class="text-muted mb-3">Manage system-wide settings and configurations for all users.</p>
                                <a href="{{ route('settings.system') }}" class="btn btn-outline-warning">
                                    System Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

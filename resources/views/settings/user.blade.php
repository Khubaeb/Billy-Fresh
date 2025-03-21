@extends('layouts.app')

@section('title', 'User Settings')
@section('page-title', 'User Settings')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}">My Profile</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <!-- Settings Navigation -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Settings</h5>
                <div>
                    @if(Auth::user()->businesses->isNotEmpty())
                        <a href="{{ route('settings.business') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-building-gear me-1"></i> Business Settings
                        </a>
                    @endif
                    @can('manageSystem', \App\Models\User::class)
                        <a href="{{ route('settings.system') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-sliders me-1"></i> System Settings
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Settings Categories</h5>
            </div>
            <div class="list-group list-group-flush">
                @foreach($groups as $groupKey => $group)
                    <a href="{{ route('settings.user', ['tab' => $groupKey]) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-center {{ $currentTab === $groupKey ? 'active' : '' }}">
                        <i class="bi bi-{{ $group['icon'] }} me-2"></i> {{ $group['title'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Account Actions</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-person me-2"></i> Edit Profile
                </a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="list-group-item list-group-item-action d-flex align-items-center text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $groups[$currentTab]['title'] }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $groups[$currentTab]['description'] }}</p>

                <form action="{{ route('settings.user.update') }}?tab={{ $currentTab }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($currentTab === 'general')
                        <!-- General Settings -->
                        <div class="mb-3">
                            <label for="default_business_id" class="form-label">Default Business</label>
                            <select class="form-select @error('default_business_id') is-invalid @enderror" id="default_business_id" name="default_business_id">
                                <option value="">None (ask each time)</option>
                                @foreach(Auth::user()->businesses as $business)
                                    <option value="{{ $business->id }}" 
                                        {{ isset($userSettings['general.default_business_id']) && $userSettings['general.default_business_id'] == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose which business should be selected by default when you log in.</div>
                            @error('default_business_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_page" class="form-label">Default Landing Page</label>
                            <select class="form-select @error('default_page') is-invalid @enderror" id="default_page" name="default_page">
                                <option value="dashboard" {{ isset($userSettings['general.default_page']) && $userSettings['general.default_page'] === 'dashboard' ? 'selected' : '' }}>
                                    Dashboard
                                </option>
                                <option value="invoices.index" {{ isset($userSettings['general.default_page']) && $userSettings['general.default_page'] === 'invoices.index' ? 'selected' : '' }}>
                                    Invoices
                                </option>
                                <option value="customers.index" {{ isset($userSettings['general.default_page']) && $userSettings['general.default_page'] === 'customers.index' ? 'selected' : '' }}>
                                    Customers
                                </option>
                                <option value="expenses.index" {{ isset($userSettings['general.default_page']) && $userSettings['general.default_page'] === 'expenses.index' ? 'selected' : '' }}>
                                    Expenses
                                </option>
                                <option value="reports.index" {{ isset($userSettings['general.default_page']) && $userSettings['general.default_page'] === 'reports.index' ? 'selected' : '' }}>
                                    Reports
                                </option>
                            </select>
                            <div class="form-text">Choose which page you want to see first after logging in.</div>
                            @error('default_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="items_per_page" class="form-label">Items Per Page</label>
                            <select class="form-select @error('items_per_page') is-invalid @enderror" id="items_per_page" name="items_per_page">
                                <option value="10" {{ isset($userSettings['general.items_per_page']) && $userSettings['general.items_per_page'] == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ isset($userSettings['general.items_per_page']) && $userSettings['general.items_per_page'] == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ isset($userSettings['general.items_per_page']) && $userSettings['general.items_per_page'] == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ isset($userSettings['general.items_per_page']) && $userSettings['general.items_per_page'] == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <div class="form-text">Number of items to show per page in lists and tables.</div>
                            @error('items_per_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'notification')
                        <!-- Notification Settings -->
                        <div class="mb-3">
                            <h6>Notification Preferences</h6>
                            <p class="text-muted">Configure how you want to receive notifications.</p>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('email_notifications') is-invalid @enderror" 
                                type="checkbox" id="email_notifications" name="email_notifications" value="1"
                                {{ isset($userSettings['notification.email_notifications']) && $userSettings['notification.email_notifications'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notifications">Email Notifications</label>
                            <div class="form-text">Receive notifications via email.</div>
                            @error('email_notifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('in_app_notifications') is-invalid @enderror" 
                                type="checkbox" id="in_app_notifications" name="in_app_notifications" value="1"
                                {{ isset($userSettings['notification.in_app_notifications']) && $userSettings['notification.in_app_notifications'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="in_app_notifications">In-App Notifications</label>
                            <div class="form-text">Receive notifications inside the application.</div>
                            @error('in_app_notifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <h6>Summary Reports</h6>
                            <p class="text-muted">Configure periodic summary reports.</p>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('daily_summary') is-invalid @enderror" 
                                type="checkbox" id="daily_summary" name="daily_summary" value="1"
                                {{ isset($userSettings['notification.daily_summary']) && $userSettings['notification.daily_summary'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="daily_summary">Daily Summary</label>
                            <div class="form-text">Receive a daily summary of all activities.</div>
                            @error('daily_summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('weekly_summary') is-invalid @enderror" 
                                type="checkbox" id="weekly_summary" name="weekly_summary" value="1"
                                {{ isset($userSettings['notification.weekly_summary']) && $userSettings['notification.weekly_summary'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="weekly_summary">Weekly Summary</label>
                            <div class="form-text">Receive a weekly summary of all activities.</div>
                            @error('weekly_summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'security')
                        <!-- Security Settings -->
                        <div class="mb-3">
                            <h6>Account Security</h6>
                            <p class="text-muted">Configure additional security measures for your account.</p>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('two_factor_auth') is-invalid @enderror" 
                                type="checkbox" id="two_factor_auth" name="two_factor_auth" value="1"
                                {{ isset($userSettings['security.two_factor_auth']) && $userSettings['security.two_factor_auth'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="two_factor_auth">Two-Factor Authentication</label>
                            <div class="form-text">Enable two-factor authentication for additional security.</div>
                            @error('two_factor_auth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(isset($userSettings['security.two_factor_auth']) && $userSettings['security.two_factor_auth'])
                            <div class="mb-4 ms-4">
                                <a href="#" class="btn btn-sm btn-outline-primary">Configure Two-Factor Authentication</a>
                            </div>
                        @endif

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('login_notification') is-invalid @enderror" 
                                type="checkbox" id="login_notification" name="login_notification" value="1"
                                {{ isset($userSettings['security.login_notification']) && $userSettings['security.login_notification'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_notification">Login Notification</label>
                            <div class="form-text">Receive email notifications when your account is accessed.</div>
                            @error('login_notification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-info-circle-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading">Security Recommendation</h6>
                                    <p class="mb-0">For the highest level of security, we recommend enabling both two-factor authentication and login notifications.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

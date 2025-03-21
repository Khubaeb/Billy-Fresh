@extends('layouts.app')

@section('title', 'Business Settings')
@section('page-title', 'Business Settings')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('businesses.show', $business) }}">{{ $business->name }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <!-- Business Selector -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="me-3">Business:</span>
                    <form method="GET" action="{{ route('settings.business') }}" class="mb-0">
                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                        <select class="form-select" name="businessId" onchange="this.form.submit()">
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ $business->id == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div>
                    <a href="{{ route('settings.user') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-person-gear me-1"></i> User Settings
                    </a>
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
                    <a href="{{ route('settings.business', ['businessId' => $business->id, 'tab' => $groupKey]) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-center {{ $currentTab === $groupKey ? 'active' : '' }}">
                        <i class="bi bi-{{ $group['icon'] }} me-2"></i> {{ $group['title'] }}
                    </a>
                @endforeach
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

                <form action="{{ route('settings.business.update', $business->id) }}?tab={{ $currentTab }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($currentTab === 'general')
                        <!-- General Settings -->
                        <div class="mb-3">
                            <label for="default_tax_rate_id" class="form-label">Default Tax Rate</label>
                            <select class="form-select @error('default_tax_rate_id') is-invalid @enderror" id="default_tax_rate_id" name="default_tax_rate_id">
                                <option value="">None</option>
                                @foreach($business->taxRates as $taxRate)
                                    <option value="{{ $taxRate->id }}" {{ $business->getSetting('general.default_tax_rate_id') == $taxRate->id ? 'selected' : '' }}>
                                        {{ $taxRate->name }} ({{ $taxRate->formatted_percentage }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">The default tax rate will be automatically selected for new invoices and services.</div>
                            @error('default_tax_rate_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_payment_term" class="form-label">Default Payment Term (Days)</label>
                            <input type="number" class="form-control @error('default_payment_term') is-invalid @enderror" 
                                id="default_payment_term" name="default_payment_term" min="0" max="90"
                                value="{{ $business->getSetting('general.default_payment_term', 30) }}">
                            <div class="form-text">The default number of days for invoice payment terms.</div>
                            @error('default_payment_term')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fiscal_year_start" class="form-label">Fiscal Year Start Date</label>
                            <input type="text" class="form-control @error('fiscal_year_start') is-invalid @enderror" 
                                id="fiscal_year_start" name="fiscal_year_start" 
                                value="{{ $business->getSetting('general.fiscal_year_start', '01-01') }}"
                                placeholder="MM-DD">
                            <div class="form-text">Format: MM-DD (e.g., 01-01 for January 1st)</div>
                            @error('fiscal_year_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('enable_customer_portal') is-invalid @enderror" 
                                type="checkbox" id="enable_customer_portal" name="enable_customer_portal" value="1"
                                {{ $business->getSetting('general.enable_customer_portal', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_customer_portal">Enable Customer Portal</label>
                            <div class="form-text">Allow customers to log in and view their invoices, estimates, and payment history.</div>
                            @error('enable_customer_portal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'invoice')
                        <!-- Invoice Settings -->
                        <div class="mb-3">
                            <label for="invoice_prefix" class="form-label">Invoice Number Prefix</label>
                            <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror" 
                                id="invoice_prefix" name="invoice_prefix" maxlength="10"
                                value="{{ $business->getSetting('invoice.invoice_prefix', 'INV-') }}">
                            <div class="form-text">A short prefix added to invoice numbers (e.g., INV-, BILL-, etc.)</div>
                            @error('invoice_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_starting_number" class="form-label">Next Invoice Number</label>
                            <input type="number" class="form-control @error('invoice_starting_number') is-invalid @enderror" 
                                id="invoice_starting_number" name="invoice_starting_number" min="1"
                                value="{{ $business->getSetting('invoice.invoice_starting_number', 1001) }}">
                            <div class="form-text">The next invoice number to be used. This will increment automatically.</div>
                            @error('invoice_starting_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_notes" class="form-label">Default Invoice Notes</label>
                            <textarea class="form-control @error('invoice_notes') is-invalid @enderror" 
                                id="invoice_notes" name="invoice_notes" rows="3">{{ $business->getSetting('invoice.invoice_notes', '') }}</textarea>
                            <div class="form-text">Default notes to appear on invoices.</div>
                            @error('invoice_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_footer" class="form-label">Default Invoice Footer</label>
                            <textarea class="form-control @error('invoice_footer') is-invalid @enderror" 
                                id="invoice_footer" name="invoice_footer" rows="2">{{ $business->getSetting('invoice.invoice_footer', 'Thank you for your business!') }}</textarea>
                            <div class="form-text">Default footer text to appear on invoices.</div>
                            @error('invoice_footer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_due_days" class="form-label">Default Invoice Due Days</label>
                            <input type="number" class="form-control @error('invoice_due_days') is-invalid @enderror" 
                                id="invoice_due_days" name="invoice_due_days" min="0" max="365"
                                value="{{ $business->getSetting('invoice.invoice_due_days', 30) }}">
                            <div class="form-text">Default number of days before an invoice is due.</div>
                            @error('invoice_due_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_reminder_days" class="form-label">Invoice Reminder Days</label>
                            <input type="text" class="form-control @error('invoice_reminder_days') is-invalid @enderror" 
                                id="invoice_reminder_days" name="invoice_reminder_days"
                                value="{{ $business->getSetting('invoice.invoice_reminder_days', '-7,-3,-1,1,3,7') }}">
                            <div class="form-text">Comma-separated list of days to send reminders. Negative numbers are days before due date, positive are days after.</div>
                            @error('invoice_reminder_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('enable_late_fees') is-invalid @enderror" 
                                type="checkbox" id="enable_late_fees" name="enable_late_fees" value="1"
                                {{ $business->getSetting('invoice.enable_late_fees', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_late_fees">Enable Late Fees</label>
                            <div class="form-text">Automatically calculate late fees for overdue invoices.</div>
                            @error('enable_late_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="late_fee_percentage" class="form-label">Late Fee Percentage</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('late_fee_percentage') is-invalid @enderror" 
                                    id="late_fee_percentage" name="late_fee_percentage" min="0" max="100" step="0.01"
                                    value="{{ $business->getSetting('invoice.late_fee_percentage', 5) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Percentage of the invoice total to charge as a late fee.</div>
                            @error('late_fee_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'notification')
                        <!-- Notification Settings -->
                        <div class="mb-3">
                            <h6>Email Notifications</h6>
                            <p class="text-muted">Select which events should trigger email notifications.</p>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('invoice_created') is-invalid @enderror" 
                                type="checkbox" id="invoice_created" name="invoice_created" value="1"
                                {{ $business->getSetting('notification.invoice_created', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="invoice_created">Invoice Created</label>
                            <div class="form-text">Send notification when a new invoice is created.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('invoice_sent') is-invalid @enderror" 
                                type="checkbox" id="invoice_sent" name="invoice_sent" value="1"
                                {{ $business->getSetting('notification.invoice_sent', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="invoice_sent">Invoice Sent</label>
                            <div class="form-text">Send notification when an invoice is sent to customer.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('invoice_paid') is-invalid @enderror" 
                                type="checkbox" id="invoice_paid" name="invoice_paid" value="1"
                                {{ $business->getSetting('notification.invoice_paid', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="invoice_paid">Invoice Paid</label>
                            <div class="form-text">Send notification when an invoice is marked as paid.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('invoice_overdue') is-invalid @enderror" 
                                type="checkbox" id="invoice_overdue" name="invoice_overdue" value="1"
                                {{ $business->getSetting('notification.invoice_overdue', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="invoice_overdue">Invoice Overdue</label>
                            <div class="form-text">Send notification when an invoice becomes overdue.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('customer_created') is-invalid @enderror" 
                                type="checkbox" id="customer_created" name="customer_created" value="1"
                                {{ $business->getSetting('notification.customer_created', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="customer_created">Customer Created</label>
                            <div class="form-text">Send notification when a new customer is created.</div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <h6>Report Notifications</h6>
                            <p class="text-muted">Select which reports should be sent automatically.</p>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('report_weekly') is-invalid @enderror" 
                                type="checkbox" id="report_weekly" name="report_weekly" value="1"
                                {{ $business->getSetting('notification.report_weekly', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="report_weekly">Weekly Summary Report</label>
                            <div class="form-text">Receive a weekly summary report of business activities.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('report_monthly') is-invalid @enderror" 
                                type="checkbox" id="report_monthly" name="report_monthly" value="1"
                                {{ $business->getSetting('notification.report_monthly', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="report_monthly">Monthly Financial Report</label>
                            <div class="form-text">Receive a monthly financial report with key metrics.</div>
                        </div>

                    @elseif($currentTab === 'locale')
                        <!-- Localization Settings -->
                        <div class="mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select @error('date_format') is-invalid @enderror" id="date_format" name="date_format">
                                <option value="Y-m-d" {{ $business->getSetting('locale.date_format', 'Y-m-d') === 'Y-m-d' ? 'selected' : '' }}>2025-03-21 (YYYY-MM-DD)</option>
                                <option value="m/d/Y" {{ $business->getSetting('locale.date_format', 'Y-m-d') === 'm/d/Y' ? 'selected' : '' }}>03/21/2025 (MM/DD/YYYY)</option>
                                <option value="d/m/Y" {{ $business->getSetting('locale.date_format', 'Y-m-d') === 'd/m/Y' ? 'selected' : '' }}>21/03/2025 (DD/MM/YYYY)</option>
                                <option value="d.m.Y" {{ $business->getSetting('locale.date_format', 'Y-m-d') === 'd.m.Y' ? 'selected' : '' }}>21.03.2025 (DD.MM.YYYY)</option>
                            </select>
                            <div class="form-text">Choose how dates are displayed throughout the system.</div>
                            @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="time_format" class="form-label">Time Format</label>
                            <select class="form-select @error('time_format') is-invalid @enderror" id="time_format" name="time_format">
                                <option value="H:i" {{ $business->getSetting('locale.time_format', 'H:i') === 'H:i' ? 'selected' : '' }}>14:30 (24-hour)</option>
                                <option value="h:i A" {{ $business->getSetting('locale.time_format', 'H:i') === 'h:i A' ? 'selected' : '' }}>2:30 PM (12-hour)</option>
                            </select>
                            <div class="form-text">Choose how times are displayed throughout the system.</div>
                            @error('time_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                @php
                                    $timezones = [
                                        'UTC' => 'UTC',
                                        'America/New_York' => 'Eastern Time (US & Canada)',
                                        'America/Chicago' => 'Central Time (US & Canada)',
                                        'America/Denver' => 'Mountain Time (US & Canada)',
                                        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
                                        'America/Anchorage' => 'Alaska',
                                        'America/Honolulu' => 'Hawaii',
                                        'Europe/London' => 'London',
                                        'Europe/Paris' => 'Paris',
                                        'Europe/Berlin' => 'Berlin',
                                        'Asia/Tokyo' => 'Tokyo',
                                        'Asia/Shanghai' => 'Shanghai',
                                        'Asia/Kolkata' => 'Mumbai',
                                        'Australia/Sydney' => 'Sydney',
                                    ];
                                    $currentTimezone = $business->getSetting('locale.timezone', 'UTC');
                                @endphp
                                
                                @foreach($timezones as $key => $name)
                                    <option value="{{ $key }}" {{ $currentTimezone === $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select your timezone for accurate date and time display.</div>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

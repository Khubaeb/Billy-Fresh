@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">System Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row">
    <!-- Admin Check Banner -->
    <div class="col-12 mb-4">
        <div class="alert alert-warning">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
                <div>
                    <h6 class="alert-heading">Administrator Access</h6>
                    <p class="mb-0">You are viewing system-wide settings that affect all users and businesses. Changes made here will immediately apply across the entire platform.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Navigation -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">System Settings</h5>
                <div>
                    <a href="{{ route('settings.user') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-person-gear me-1"></i> User Settings
                    </a>
                    @if(Auth::user()->businesses->isNotEmpty())
                        <a href="{{ route('settings.business') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-building-gear me-1"></i> Business Settings
                        </a>
                    @endif
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
                    <a href="{{ route('settings.system', ['tab' => $groupKey]) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-center {{ $currentTab === $groupKey ? 'active' : '' }}">
                        <i class="bi bi-{{ $group['icon'] }} me-2"></i> {{ $group['title'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-6">App Version</dt>
                    <dd class="col-sm-6">1.0.0</dd>
                    
                    <dt class="col-sm-6">PHP Version</dt>
                    <dd class="col-sm-6">{{ phpversion() }}</dd>
                    
                    <dt class="col-sm-6">Laravel</dt>
                    <dd class="col-sm-6">{{ app()->version() }}</dd>
                    
                    <dt class="col-sm-6">Environment</dt>
                    <dd class="col-sm-6">{{ app()->environment() }}</dd>
                </dl>
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

                <form action="{{ route('settings.system.update') }}?tab={{ $currentTab }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($currentTab === 'general')
                        <!-- General System Settings -->
                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                id="app_name" name="app_name" 
                                value="{{ isset($systemSettings['general.app_name']) ? $systemSettings['general.app_name'] : config('app.name', 'Billy') }}">
                            <div class="form-text">The name of your application displayed in the browser title and emails.</div>
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                id="company_name" name="company_name" 
                                value="{{ $systemSettings['general.company_name'] ?? 'Your Company' }}">
                            <div class="form-text">The name of your company displayed in documentation and emails.</div>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Support Email</label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                id="contact_email" name="contact_email" 
                                value="{{ $systemSettings['general.contact_email'] ?? 'support@example.com' }}">
                            <div class="form-text">Email address displayed for support inquiries.</div>
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="support_phone" class="form-label">Support Phone</label>
                            <input type="text" class="form-control @error('support_phone') is-invalid @enderror" 
                                id="support_phone" name="support_phone" 
                                value="{{ $systemSettings['general.support_phone'] ?? '' }}">
                            <div class="form-text">Phone number displayed for support inquiries (optional).</div>
                            @error('support_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_language" class="form-label">Default Language</label>
                            <select class="form-select @error('default_language') is-invalid @enderror" 
                                id="default_language" name="default_language">
                                <option value="en" {{ ($systemSettings['general.default_language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="es" {{ ($systemSettings['general.default_language'] ?? 'en') === 'es' ? 'selected' : '' }}>Spanish</option>
                                <option value="fr" {{ ($systemSettings['general.default_language'] ?? 'en') === 'fr' ? 'selected' : '' }}>French</option>
                                <option value="de" {{ ($systemSettings['general.default_language'] ?? 'en') === 'de' ? 'selected' : '' }}>German</option>
                            </select>
                            <div class="form-text">Default language for new users.</div>
                            @error('default_language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'email')
                        <!-- Email Configuration -->
                        <div class="mb-3">
                            <label for="mail_driver" class="form-label">Mail Driver</label>
                            <select class="form-select @error('mail_driver') is-invalid @enderror" 
                                id="mail_driver" name="mail_driver">
                                <option value="smtp" {{ ($systemSettings['email.mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($systemSettings['email.mail_driver'] ?? 'smtp') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ ($systemSettings['email.mail_driver'] ?? 'smtp') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ ($systemSettings['email.mail_driver'] ?? 'smtp') === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ ($systemSettings['email.mail_driver'] ?? 'smtp') === 'postmark' ? 'selected' : '' }}>Postmark</option>
                            </select>
                            <div class="form-text">Select the mail driver for sending emails.</div>
                            @error('mail_driver')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control @error('mail_host') is-invalid @enderror" 
                                id="mail_host" name="mail_host" 
                                value="{{ $systemSettings['email.mail_host'] ?? 'smtp.mailtrap.io' }}">
                            <div class="form-text">SMTP server hostname (e.g., smtp.gmail.com).</div>
                            @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_port" class="form-label">SMTP Port</label>
                            <input type="number" class="form-control @error('mail_port') is-invalid @enderror" 
                                id="mail_port" name="mail_port" 
                                value="{{ $systemSettings['email.mail_port'] ?? '2525' }}">
                            <div class="form-text">SMTP server port (e.g., 587 for TLS, 465 for SSL).</div>
                            @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_username" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control @error('mail_username') is-invalid @enderror" 
                                id="mail_username" name="mail_username" 
                                value="{{ $systemSettings['email.mail_username'] ?? '' }}">
                            <div class="form-text">SMTP authentication username.</div>
                            @error('mail_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_password" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control @error('mail_password') is-invalid @enderror" 
                                id="mail_password" name="mail_password" 
                                value="{{ $systemSettings['email.mail_password'] ?? '' }}">
                            <div class="form-text">SMTP authentication password.</div>
                            @error('mail_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_encryption" class="form-label">Encryption</label>
                            <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                id="mail_encryption" name="mail_encryption">
                                <option value="tls" {{ ($systemSettings['email.mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($systemSettings['email.mail_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ ($systemSettings['email.mail_encryption'] ?? 'tls') === '' ? 'selected' : '' }}>None</option>
                            </select>
                            <div class="form-text">SMTP encryption protocol.</div>
                            @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_from_address" class="form-label">From Address</label>
                            <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" 
                                id="mail_from_address" name="mail_from_address" 
                                value="{{ $systemSettings['email.mail_from_address'] ?? 'hello@example.com' }}">
                            <div class="form-text">Email address that system emails will be sent from.</div>
                            @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_from_name" class="form-label">From Name</label>
                            <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" 
                                id="mail_from_name" name="mail_from_name" 
                                value="{{ $systemSettings['email.mail_from_name'] ?? 'Billy' }}">
                            <div class="form-text">Name that system emails will be sent from.</div>
                            @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex mt-4">
                            <button type="button" class="btn btn-outline-primary" id="test-email-btn">
                                <i class="bi bi-envelope me-1"></i> Send Test Email
                            </button>
                        </div>

                    @elseif($currentTab === 'security')
                        <!-- Security Configuration -->
                        <div class="mb-3">
                            <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                            <input type="number" class="form-control @error('max_login_attempts') is-invalid @enderror" 
                                id="max_login_attempts" name="max_login_attempts" min="3" max="10"
                                value="{{ $systemSettings['security.max_login_attempts'] ?? 5 }}">
                            <div class="form-text">Maximum number of failed login attempts before account lockout.</div>
                            @error('max_login_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_expiration_days" class="form-label">Password Expiration Days</label>
                            <input type="number" class="form-control @error('password_expiration_days') is-invalid @enderror" 
                                id="password_expiration_days" name="password_expiration_days" min="0"
                                value="{{ $systemSettings['security.password_expiration_days'] ?? 90 }}">
                            <div class="form-text">Number of days before passwords expire (0 for never).</div>
                            @error('password_expiration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="session_lifetime_minutes" class="form-label">Session Lifetime (Minutes)</label>
                            <input type="number" class="form-control @error('session_lifetime_minutes') is-invalid @enderror" 
                                id="session_lifetime_minutes" name="session_lifetime_minutes" min="5"
                                value="{{ $systemSettings['security.session_lifetime_minutes'] ?? 120 }}">
                            <div class="form-text">How long a user session remains active before requiring re-login.</div>
                            @error('session_lifetime_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('enforce_2fa_admins') is-invalid @enderror" 
                                type="checkbox" id="enforce_2fa_admins" name="enforce_2fa_admins" value="1"
                                {{ isset($systemSettings['security.enforce_2fa_admins']) && $systemSettings['security.enforce_2fa_admins'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="enforce_2fa_admins">Enforce 2FA for Administrators</label>
                            <div class="form-text">Require two-factor authentication for all administrative users.</div>
                            @error('enforce_2fa_admins')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($currentTab === 'integration')
                        <!-- API & Integrations -->
                        <div class="mb-3">
                            <h6>Stripe Integration</h6>
                            <p class="text-muted">Configure Stripe for online payment processing.</p>
                        </div>

                        <div class="mb-3">
                            <label for="stripe_key" class="form-label">Stripe Publishable Key</label>
                            <input type="text" class="form-control @error('stripe_key') is-invalid @enderror" 
                                id="stripe_key" name="stripe_key" 
                                value="{{ $systemSettings['integration.stripe_key'] ?? '' }}">
                            <div class="form-text">Your Stripe publishable key for the client-side integration.</div>
                            @error('stripe_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="stripe_secret" class="form-label">Stripe Secret Key</label>
                            <input type="password" class="form-control @error('stripe_secret') is-invalid @enderror" 
                                id="stripe_secret" name="stripe_secret" 
                                value="{{ $systemSettings['integration.stripe_secret'] ?? '' }}">
                            <div class="form-text">Your Stripe secret key for server-side API calls.</div>
                            @error('stripe_secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <h6>Google Analytics</h6>
                            <p class="text-muted">Configure Google Analytics for website tracking.</p>
                        </div>

                        <div class="mb-3">
                            <label for="google_analytics_id" class="form-label">Google Analytics ID</label>
                            <input type="text" class="form-control @error('google_analytics_id') is-invalid @enderror" 
                                id="google_analytics_id" name="google_analytics_id" 
                                value="{{ $systemSettings['integration.google_analytics_id'] ?? '' }}">
                            <div class="form-text">Your Google Analytics tracking ID (e.g., UA-XXXXXXXXX-X).</div>
                            @error('google_analytics_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <h6>reCAPTCHA</h6>
                            <p class="text-muted">Configure Google reCAPTCHA to protect forms from spam.</p>
                        </div>

                        <div class="mb-3">
                            <label for="recaptcha_site_key" class="form-label">reCAPTCHA Site Key</label>
                            <input type="text" class="form-control @error('recaptcha_site_key') is-invalid @enderror" 
                                id="recaptcha_site_key" name="recaptcha_site_key" 
                                value="{{ $systemSettings['integration.recaptcha_site_key'] ?? '' }}">
                            <div class="form-text">Your Google reCAPTCHA site key.</div>
                            @error('recaptcha_site_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="recaptcha_secret_key" class="form-label">reCAPTCHA Secret Key</label>
                            <input type="password" class="form-control @error('recaptcha_secret_key') is-invalid @enderror" 
                                id="recaptcha_secret_key" name="recaptcha_secret_key" 
                                value="{{ $systemSettings['integration.recaptcha_secret_key'] ?? '' }}">
                            <div class="form-text">Your Google reCAPTCHA secret key.</div>
                            @error('recaptcha_secret_key')
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

@if($currentTab === 'email')
<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">Send Test Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="testEmailForm">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Recipient Email</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" value="{{ Auth::user()->email }}" required>
                        <div class="form-text">Where to send the test email. Defaults to your email address.</div>
                    </div>
                </form>
                <div id="testEmailResult" class="d-none mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="sendTestEmailBtn">
                    <i class="bi bi-envelope me-1"></i> Send Test Email
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testEmailBtn = document.getElementById('test-email-btn');
        const sendTestEmailBtn = document.getElementById('sendTestEmailBtn');
        const testEmailResult = document.getElementById('testEmailResult');
        
        // Show the modal when the test email button is clicked
        testEmailBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('testEmailModal'));
            modal.show();
        });
        
        // Send test email when the send button is clicked
        sendTestEmailBtn.addEventListener('click', function() {
            const email = document.getElementById('test_email').value;
            
            // Show loading state
            sendTestEmailBtn.disabled = true;
            sendTestEmailBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
            
            testEmailResult.className = 'd-none';
            
            // Simulate sending test email (in a real app, this would be an AJAX call to a backend route)
            setTimeout(function() {
                // Show success message
                testEmailResult.className = 'alert alert-success';
                testEmailResult.innerHTML = '<i class="bi bi-check-circle me-2"></i> Test email sent successfully to ' + email;
                
                // Reset button
                sendTestEmailBtn.disabled = false;
                sendTestEmailBtn.innerHTML = '<i class="bi bi-envelope me-1"></i> Send Test Email';
            }, 2000);
        });
    });
</script>
@endpush
@endif
@endsection

@extends('layouts.app')

@section('title', 'User Settings')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h5 mb-0">{{ __('User Settings') }}</h1>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Theme Settings -->
                        <div class="mb-4">
                            <h3 class="h5 mb-3">{{ __('Theme Preferences') }}</h3>
                            
                            <div class="mb-3">
                                <label for="theme" class="form-label">{{ __('Theme') }}</label>
                                <select class="form-select @error('theme') is-invalid @enderror" id="theme" name="theme">
                                    <option value="light" {{ (Auth::user()->settings['theme'] ?? '') == 'light' ? 'selected' : '' }}>{{ __('Light') }}</option>
                                    <option value="dark" {{ (Auth::user()->settings['theme'] ?? '') == 'dark' ? 'selected' : '' }}>{{ __('Dark') }}</option>
                                    <option value="system" {{ (Auth::user()->settings['theme'] ?? '') == 'system' ? 'selected' : '' }}>{{ __('System Default') }}</option>
                                </select>
                                
                                @error('theme')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Localization Settings -->
                        <div class="mb-4">
                            <h3 class="h5 mb-3">{{ __('Localization') }}</h3>
                            
                            <div class="mb-3">
                                <label for="language" class="form-label">{{ __('Language') }}</label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                                    <option value="en" {{ (Auth::user()->settings['language'] ?? '') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="es" {{ (Auth::user()->settings['language'] ?? '') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="fr" {{ (Auth::user()->settings['language'] ?? '') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                </select>
                                
                                @error('language')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                    @foreach (timezone_identifiers_list() as $timezone)
                                        <option value="{{ $timezone }}" {{ (Auth::user()->settings['timezone'] ?? '') == $timezone ? 'selected' : '' }}>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                @error('timezone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_format" class="form-label">{{ __('Date Format') }}</label>
                                <select class="form-select @error('date_format') is-invalid @enderror" id="date_format" name="date_format">
                                    <option value="m/d/Y" {{ (Auth::user()->settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ date('m/d/Y') }})</option>
                                    <option value="d/m/Y" {{ (Auth::user()->settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                                    <option value="Y-m-d" {{ (Auth::user()->settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                                </select>
                                
                                @error('date_format')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Notification Preferences -->
                        <div class="mb-4">
                            <h3 class="h5 mb-3">{{ __('Notification Preferences') }}</h3>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifications_email" name="notification_preferences[email]" 
                                    {{ isset(Auth::user()->settings['notification_preferences']['email']) && Auth::user()->settings['notification_preferences']['email'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifications_email">
                                    {{ __('Receive email notifications') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifications_system" name="notification_preferences[system]"
                                    {{ isset(Auth::user()->settings['notification_preferences']['system']) && Auth::user()->settings['notification_preferences']['system'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifications_system">
                                    {{ __('Receive system notifications') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifications_invoice" name="notification_preferences[invoice]"
                                    {{ isset(Auth::user()->settings['notification_preferences']['invoice']) && Auth::user()->settings['notification_preferences']['invoice'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifications_invoice">
                                    {{ __('Invoice notifications') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifications_payment" name="notification_preferences[payment]"
                                    {{ isset(Auth::user()->settings['notification_preferences']['payment']) && Auth::user()->settings['notification_preferences']['payment'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifications_payment">
                                    {{ __('Payment notifications') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Save Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

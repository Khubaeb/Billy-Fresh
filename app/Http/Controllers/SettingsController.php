<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display business settings view.
     */
    public function business(Request $request, ?int $businessId = null): View
    {
        // If no business ID provided, get the first business of the user
        if (!$businessId) {
            $firstBusiness = Auth::user()->businesses()->first();
            if ($firstBusiness) {
                $businessId = $firstBusiness->id;
            }
        }
        
        // If we have a business ID, get that business
        if ($businessId) {
            $business = Business::findOrFail($businessId);
            $this->authorize('view', $business);
        } else {
            // No businesses found
            return view('settings.no-business');
        }
        
        // Get all businesses for the dropdown
        $businesses = Auth::user()->businesses;
        
        // Get all settings for this business
        $settings = $business->settings ?? [];
        
        // Define available settings groups
        $groups = [
            'general' => [
                'title' => 'General Settings',
                'icon' => 'gear',
                'description' => 'Basic business settings like default values and preferences.',
            ],
            'invoice' => [
                'title' => 'Invoice Settings',
                'icon' => 'receipt',
                'description' => 'Settings for invoice numbering, templates, and payment terms.',
            ],
            'notification' => [
                'title' => 'Notification Settings',
                'icon' => 'bell',
                'description' => 'Email notification preferences for various system events.',
            ],
            'locale' => [
                'title' => 'Localization',
                'icon' => 'globe',
                'description' => 'Date format, time zone, and other region-specific settings.',
            ],
        ];
        
        // Get the current tab or default to 'general'
        $currentTab = $request->query('tab', 'general');
        
        return view('settings.business', compact('business', 'businesses', 'settings', 'groups', 'currentTab'));
    }
    
    /**
     * Update business settings.
     */
    public function updateBusiness(Request $request, int $businessId): RedirectResponse
    {
        $business = Business::findOrFail($businessId);
        $this->authorize('update', $business);
        
        // Get the current tab
        $tab = $request->query('tab', 'general');
        
        // Validate based on the current tab
        $validated = $this->validateSettings($request, $tab);
        
        // Update settings
        foreach ($validated as $key => $value) {
            // Skip CSRF token
            if ($key === '_token') continue;
            
            // Save the setting
            $business->setSetting($tab . '.' . $key, $value);
        }
        
        // Save the business
        $business->save();
        
        return redirect()->route('settings.business', [
            'businessId' => $business->id,
            'tab' => $tab
        ])->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Display user settings view.
     */
    public function user(): View
    {
        $user = Auth::user();
        
        // Get all settings for this user
        $userSettings = Setting::getAllForEntity(User::class, $user->id);
        
        // Define available settings groups
        $groups = [
            'general' => [
                'title' => 'General Settings',
                'icon' => 'gear',
                'description' => 'Basic user preferences.',
            ],
            'notification' => [
                'title' => 'Notification Settings',
                'icon' => 'bell',
                'description' => 'Email and in-app notification preferences.',
            ],
            'security' => [
                'title' => 'Security Settings',
                'icon' => 'shield-lock',
                'description' => 'Authentication and account security settings.',
            ],
        ];
        
        // Get the current tab or default to 'general'
        $currentTab = request()->query('tab', 'general');
        
        return view('settings.user', compact('user', 'userSettings', 'groups', 'currentTab'));
    }
    
    /**
     * Update user settings.
     */
    public function updateUser(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Get the current tab
        $tab = $request->query('tab', 'general');
        
        // Validate based on the current tab
        $validated = $this->validateUserSettings($request, $tab);
        
        // Update settings
        foreach ($validated as $key => $value) {
            // Skip CSRF token
            if ($key === '_token') continue;
            
            // Save the setting using the Setting model
            Setting::setValue(User::class, $user->id, $tab . '.' . $key, $value);
        }
        
        return redirect()->route('settings.user', ['tab' => $tab])
            ->with('success', 'User settings updated successfully.');
    }
    
    /**
     * Display system settings view (admin only).
     */
    public function system(): View
    {
        // Ensure user is admin
        $this->authorize('manageSystem', Auth::user());
        
        // Get system settings from the Setting model
        $systemSettings = Setting::getAllForEntity('System', 1);
        
        // Define available settings groups
        $groups = [
            'general' => [
                'title' => 'General System Settings',
                'icon' => 'gear',
                'description' => 'Global application settings.',
            ],
            'email' => [
                'title' => 'Email Configuration',
                'icon' => 'envelope',
                'description' => 'Email server settings for system notifications.',
            ],
            'security' => [
                'title' => 'Security Configuration',
                'icon' => 'shield-lock',
                'description' => 'System-wide security settings.',
            ],
            'integration' => [
                'title' => 'API & Integrations',
                'icon' => 'box-arrow-up-right',
                'description' => 'Third-party integrations and API settings.',
            ],
        ];
        
        // Get the current tab or default to 'general'
        $currentTab = request()->query('tab', 'general');
        
        return view('settings.system', compact('systemSettings', 'groups', 'currentTab'));
    }
    
    /**
     * Update system settings (admin only).
     */
    public function updateSystem(Request $request): RedirectResponse
    {
        // Ensure user is admin
        $this->authorize('manageSystem', Auth::user());
        
        // Get the current tab
        $tab = $request->query('tab', 'general');
        
        // Validate based on the current tab
        $validated = $this->validateSystemSettings($request, $tab);
        
        // Update settings
        foreach ($validated as $key => $value) {
            // Skip CSRF token
            if ($key === '_token') continue;
            
            // Save the setting using the Setting model
            Setting::setValue('System', 1, $tab . '.' . $key, $value);
        }
        
        return redirect()->route('settings.system', ['tab' => $tab])
            ->with('success', 'System settings updated successfully.');
    }
    
    /**
     * Validate settings based on the tab.
     */
    private function validateSettings(Request $request, string $tab): array
    {
        switch ($tab) {
            case 'general':
                return $request->validate([
                    'default_tax_rate_id' => 'nullable|exists:tax_rates,id',
                    'default_payment_term' => 'nullable|integer|min:0|max:90',
                    'fiscal_year_start' => 'nullable|date_format:m-d',
                    'enable_customer_portal' => 'nullable|boolean',
                ]);
                
            case 'invoice':
                return $request->validate([
                    'invoice_prefix' => 'nullable|string|max:10',
                    'invoice_starting_number' => 'nullable|integer|min:1',
                    'invoice_notes' => 'nullable|string|max:1000',
                    'invoice_footer' => 'nullable|string|max:1000',
                    'invoice_due_days' => 'nullable|integer|min:0|max:365',
                    'invoice_reminder_days' => 'nullable|string', // Comma-separated list of days
                    'enable_late_fees' => 'nullable|boolean',
                    'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
                ]);
                
            case 'notification':
                return $request->validate([
                    'invoice_created' => 'nullable|boolean',
                    'invoice_sent' => 'nullable|boolean',
                    'invoice_paid' => 'nullable|boolean',
                    'invoice_overdue' => 'nullable|boolean',
                    'customer_created' => 'nullable|boolean',
                    'report_weekly' => 'nullable|boolean',
                    'report_monthly' => 'nullable|boolean',
                ]);
                
            case 'locale':
                return $request->validate([
                    'date_format' => 'nullable|string|in:Y-m-d,m/d/Y,d/m/Y,d.m.Y',
                    'time_format' => 'nullable|string|in:H:i,h:i A',
                    'timezone' => 'nullable|string|timezone',
                ]);
                
            default:
                return [];
        }
    }
    
    /**
     * Validate user settings based on the tab.
     */
    private function validateUserSettings(Request $request, string $tab): array
    {
        switch ($tab) {
            case 'general':
                return $request->validate([
                    'default_business_id' => 'nullable|exists:businesses,id',
                    'default_page' => 'nullable|string',
                    'items_per_page' => 'nullable|integer|min:5|max:100',
                ]);
                
            case 'notification':
                return $request->validate([
                    'email_notifications' => 'nullable|boolean',
                    'in_app_notifications' => 'nullable|boolean',
                    'daily_summary' => 'nullable|boolean',
                    'weekly_summary' => 'nullable|boolean',
                ]);
                
            case 'security':
                return $request->validate([
                    'two_factor_auth' => 'nullable|boolean',
                    'login_notification' => 'nullable|boolean',
                ]);
                
            default:
                return [];
        }
    }
    
    /**
     * Validate system settings based on the tab.
     */
    private function validateSystemSettings(Request $request, string $tab): array
    {
        switch ($tab) {
            case 'general':
                return $request->validate([
                    'app_name' => 'nullable|string|max:255',
                    'company_name' => 'nullable|string|max:255',
                    'contact_email' => 'nullable|email',
                    'support_phone' => 'nullable|string|max:20',
                    'default_language' => 'nullable|string|max:10',
                ]);
                
            case 'email':
                return $request->validate([
                    'mail_driver' => 'nullable|string|in:smtp,sendmail,mailgun,ses,postmark',
                    'mail_host' => 'nullable|string|max:255',
                    'mail_port' => 'nullable|integer',
                    'mail_username' => 'nullable|string|max:255',
                    'mail_password' => 'nullable|string|max:255',
                    'mail_encryption' => 'nullable|string|in:tls,ssl',
                    'mail_from_address' => 'nullable|email',
                    'mail_from_name' => 'nullable|string|max:255',
                ]);
                
            case 'security':
                return $request->validate([
                    'max_login_attempts' => 'nullable|integer|min:3|max:10',
                    'password_expiration_days' => 'nullable|integer|min:0',
                    'session_lifetime_minutes' => 'nullable|integer|min:5',
                    'enforce_2fa_admins' => 'nullable|boolean',
                ]);
                
            case 'integration':
                return $request->validate([
                    'stripe_key' => 'nullable|string',
                    'stripe_secret' => 'nullable|string',
                    'google_analytics_id' => 'nullable|string',
                    'recaptcha_site_key' => 'nullable|string',
                    'recaptcha_secret_key' => 'nullable|string',
                ]);
                
            default:
                return [];
        }
    }
}

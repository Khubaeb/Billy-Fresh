<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserSettingsController extends Controller
{
    /**
     * Display the user settings page.
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('settings.user.index', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's settings.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'notification_preferences' => 'nullable|array',
            'theme' => 'nullable|string|in:light,dark,system',
            'language' => 'nullable|string|in:en,es,fr',
            'timezone' => 'nullable|timezone',
            'date_format' => 'nullable|string',
        ]);
        
        // Save each setting individually using the Setting model
        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            
            \App\Models\Setting::setValue('App\\Models\\User', $user->id, $key, $value);
        }
        
        return redirect()->route('user.settings.index')
            ->with('success', 'Settings updated successfully');
    }
    
    /**
     * Get user setting value with default fallback
     */
    private function getUserSetting($user, $key, $default = null)
    {
        return \App\Models\Setting::getValue('App\\Models\\User', $user->id, $key, $default);
    }
}

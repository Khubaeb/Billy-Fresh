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
        
        // Update user settings
        $settings = $user->settings ?? [];
        $updatedSettings = array_merge($settings, $validated);
        
        // Save settings
        $user->settings = $updatedSettings;
        $user->save();
        
        return redirect()->route('user.settings.index')
            ->with('success', 'Settings updated successfully');
    }
}

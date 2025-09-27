<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('settings.index', compact('user'));
    }

    public function updateEmailPreferences(Request $request)
    {
        $request->validate([
            'email_notifications_enabled' => 'boolean',
            'email_on_rent_received' => 'boolean',
            'email_on_rent_late' => 'boolean',
            'email_on_rent_partial' => 'boolean',
        ]);

        $user = auth()->user();

        $user->update([
            'email_notifications_enabled' => $request->has('email_notifications_enabled'),
            'email_on_rent_received' => $request->has('email_on_rent_received'),
            'email_on_rent_late' => $request->has('email_on_rent_late'),
            'email_on_rent_partial' => $request->has('email_on_rent_partial'),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Email preferences updated successfully');
    }
}

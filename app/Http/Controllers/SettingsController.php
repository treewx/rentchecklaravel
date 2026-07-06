<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // DB cascades remove properties, rent checks, transactions and the
        // Akahu credentials (bank tokens) along with the user
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account and all associated data have been deleted.');
    }
}

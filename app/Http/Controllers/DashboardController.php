<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RentCheck;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $properties = $user->properties()->with('rentChecks')->get();

        $upcomingRent = RentCheck::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->where('due_date', '>=', now())
        ->where('due_date', '<=', now()->addDays(7))
        ->with('property')
        ->orderBy('due_date')
        ->get();

        $overdueRent = RentCheck::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->where('due_date', '<', now())
        ->with('property')
        ->orderBy('due_date')
        ->get();

        return view('dashboard', compact('properties', 'upcomingRent', 'overdueRent'));
    }
}
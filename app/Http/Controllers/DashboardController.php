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

        // Day-based comparisons: a check due today is "upcoming" all day, and
        // only counts as overdue from the day after its due date (rent isn't
        // marked late until the morning after - see RentCheck::lateAfter())
        $upcomingRent = RentCheck::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->whereDate('due_date', '>=', today())
        ->whereDate('due_date', '<=', today()->addDays(7))
        ->with('property')
        ->orderBy('due_date')
        ->get();

        $overdueRent = RentCheck::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        // 'pending' = not yet checked, 'late'/'partial' = checked and not fully paid
        ->whereIn('status', ['pending', 'late', 'partial'])
        ->whereDate('due_date', '<', today())
        ->with('property')
        ->orderBy('due_date')
        ->get();

        return view('dashboard', compact('properties', 'upcomingRent', 'overdueRent'));
    }
}
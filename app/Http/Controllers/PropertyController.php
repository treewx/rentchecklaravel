<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RentCheck;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = auth()->user()->properties()->latest()->get();
        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->akahuCredentials) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect your Akahu account first');
        }

        $accounts = $user->akahuCredentials->accounts ?? [];

        return view('properties.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'rent_due_day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'rent_frequency' => ['required', 'string', 'in:weekly,fortnightly,monthly'],
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'bank_statement_keyword' => ['required', 'string', 'max:255'],
        ]);

        $property = auth()->user()->properties()->create($request->all());

        $this->createInitialRentCheck($property);

        return redirect()->route('properties.index')
            ->with('success', 'Property added successfully');
    }

    public function show(Property $property)
    {
        $this->authorize('view', $property);

        $rentChecks = $property->rentChecks()
            ->orderBy('due_date', 'desc')
            ->paginate(10);

        return view('properties.show', compact('property', 'rentChecks'));
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);

        $accounts = auth()->user()->akahuCredentials?->accounts ?? [];
        return view('properties.edit', compact('property', 'accounts'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'rent_due_day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'rent_frequency' => ['required', 'string', 'in:weekly,fortnightly,monthly'],
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'bank_statement_keyword' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $property->update($request->all());

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property updated successfully');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted successfully');
    }

    private function createInitialRentCheck(Property $property)
    {
        $nextDueDate = $property->next_rent_due_date;

        RentCheck::create([
            'property_id' => $property->id,
            'due_date' => $nextDueDate,
            'expected_amount' => $property->rent_amount,
            'status' => 'pending',
        ]);
    }
}
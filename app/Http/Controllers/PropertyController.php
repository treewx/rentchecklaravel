<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RentCheck;
use App\Services\AkahuService;
use Carbon\Carbon;
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

    public function getTransactionsForKeyword(Request $request)
    {
        $request->validate([
            'rent_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();

        if (!$user->akahuCredentials) {
            return response()->json(['error' => 'No Akahu credentials found'], 400);
        }

        $rentAmount = (float) $request->rent_amount;
        $tolerance = $rentAmount * 0.2; // 20% tolerance

        $akahuService = app(AkahuService::class);

        // Get transactions from the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        $accounts = $akahuService->getAccounts($user);
        $allTransactions = [];

        foreach ($accounts as $account) {
            try {
                $accountTransactions = $akahuService->getTransactions(
                    $user,
                    $account['_id'],
                    $startDate,
                    $endDate
                );
                $allTransactions = array_merge($allTransactions, $accountTransactions);
            } catch (\Exception $e) {
                continue;
            }
        }

        // Filter transactions: negative amounts (outgoing) within tolerance of rent amount
        $filteredTransactions = array_filter($allTransactions, function($transaction) use ($rentAmount, $tolerance) {
            $amount = abs((float) $transaction['amount']);
            return $transaction['amount'] < 0 &&
                   $amount >= ($rentAmount - $tolerance) &&
                   $amount <= ($rentAmount + $tolerance);
        });

        // Sort by date descending
        usort($filteredTransactions, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Format for response
        $formattedTransactions = array_map(function($transaction) {
            return [
                'id' => $transaction['_id'],
                'date' => $transaction['date'],
                'amount' => abs((float) $transaction['amount']),
                'description' => $transaction['description'] ?? '',
                'merchant' => $transaction['merchant']['name'] ?? '',
                'reference' => $transaction['meta']['reference'] ?? '',
                'day_of_week' => Carbon::parse($transaction['date'])->dayOfWeek,
            ];
        }, $filteredTransactions);

        return response()->json([
            'transactions' => array_values($formattedTransactions)
        ]);
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
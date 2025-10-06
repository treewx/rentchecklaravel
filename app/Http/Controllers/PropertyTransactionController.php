<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\RentCheck;
use Illuminate\Http\Request;

class PropertyTransactionController extends Controller
{
    /**
     * Store a new manual payment transaction
     */
    public function store(Request $request, Property $property)
    {
        $this->authorize('view', $property);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'rent_check_id' => ['nullable', 'exists:rent_checks,id'],
        ]);

        // If a rent check is specified, verify it belongs to this property
        if (isset($validated['rent_check_id'])) {
            $rentCheck = RentCheck::find($validated['rent_check_id']);
            if ($rentCheck && $rentCheck->property_id !== $property->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected rent check does not belong to this property.',
                ], 422);
            }
        }

        // Create the transaction
        $transaction = PropertyTransaction::create([
            'property_id' => $property->id,
            'rent_check_id' => $validated['rent_check_id'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'amount' => $validated['amount'], // Positive = credit (payment)
            'type' => 'manual_payment',
            'description' => $validated['description'] ?? 'Manual payment entry',
            'source' => 'manual',
            'created_by_user_id' => auth()->id(),
        ]);

        // Update property balance
        $property->updateBalance();

        // If linked to a rent check, update the rent check status
        if ($transaction->rent_check_id) {
            $rentCheck = RentCheck::find($transaction->rent_check_id);

            // Calculate total payments for this rent check
            $totalPayments = PropertyTransaction::where('rent_check_id', $rentCheck->id)
                ->where('type', '!=', 'rent_due')
                ->sum('amount');

            $rentCheck->received_amount = $totalPayments;
            $rentCheck->received_at = now();

            if ($totalPayments >= $rentCheck->expected_amount) {
                $rentCheck->status = 'received';
            } elseif ($totalPayments > 0) {
                $rentCheck->status = 'partial';
            }

            $rentCheck->save();
        }

        // Reload property with fresh balance
        $property->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully',
            'transaction' => $transaction,
            'new_balance' => $property->current_balance,
            'formatted_balance' => $property->formatted_balance,
        ]);
    }

    /**
     * Get transactions for a property
     */
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $transactions = $property->transactions()
            ->with(['rentCheck', 'createdBy'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($transactions);
    }
}

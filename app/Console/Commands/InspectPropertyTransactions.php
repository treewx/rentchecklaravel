<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\RentCheck;
use Illuminate\Console\Command;

class InspectPropertyTransactions extends Command
{
    protected $signature = 'rent:inspect-transactions {property_id}';
    protected $description = 'Inspect all transactions for a property to debug balance issues';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $property = Property::find($propertyId);

        if (!$property) {
            $this->error("Property #{$propertyId} not found!");
            return 1;
        }

        $this->info("Property: {$property->name}");
        $this->info("Current Balance: \${$property->current_balance}");
        $this->line(str_repeat("=", 80));
        $this->newLine();

        // Get all transactions
        $transactions = PropertyTransaction::where('property_id', $property->id)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $this->info("All Transactions:");
        $this->line(str_repeat("-", 80));

        $runningBalance = 0;
        foreach ($transactions as $transaction) {
            $runningBalance += $transaction->amount;

            $this->line(sprintf(
                "ID: %-4d | Date: %s | Type: %-20s | Amount: %8s | Balance: %8s | RentCheck: %s | Source: %s",
                $transaction->id,
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->type,
                number_format($transaction->amount, 2),
                number_format($runningBalance, 2),
                $transaction->rent_check_id ?? 'N/A',
                $transaction->source
            ));

            if ($transaction->description) {
                $this->line("    Description: {$transaction->description}");
            }
        }

        $this->line(str_repeat("-", 80));
        $this->info("Final Balance: $" . number_format($runningBalance, 2));
        $this->info("Expected Balance: $0.00");
        $this->newLine();

        // Group by rent check to find duplicates
        $this->info("Transactions by Rent Check:");
        $this->line(str_repeat("=", 80));

        $byRentCheck = $transactions->groupBy('rent_check_id');

        foreach ($byRentCheck as $rentCheckId => $checkTransactions) {
            if ($rentCheckId === null) {
                $this->warn("\nNo Rent Check Association:");
            } else {
                $rentCheck = RentCheck::find($rentCheckId);
                $this->info("\nRent Check #{$rentCheckId} - Due: {$rentCheck->due_date->format('M j, Y')} - Status: {$rentCheck->status}");
            }

            $this->line(str_repeat("-", 80));

            $debits = $checkTransactions->where('amount', '<', 0)->sum('amount');
            $credits = $checkTransactions->where('amount', '>', 0)->sum('amount');

            foreach ($checkTransactions as $t) {
                $this->line(sprintf(
                    "  ID: %-4d | %s | Type: %-20s | Amount: %8s | Source: %s",
                    $t->id,
                    $t->transaction_date->format('Y-m-d'),
                    $t->type,
                    number_format($t->amount, 2),
                    $t->source
                ));
            }

            $this->line("  Total Debits:  $" . number_format(abs($debits), 2));
            $this->line("  Total Credits: $" . number_format($credits, 2));
            $this->line("  Net:           $" . number_format($debits + $credits, 2));

            if (abs($debits + $credits) > 0.01) {
                $this->error("  ⚠️  WARNING: This rent check is not balanced!");
            }
        }

        return 0;
    }
}

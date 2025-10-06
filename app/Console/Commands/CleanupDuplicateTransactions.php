<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\RentCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateTransactions extends Command
{
    protected $signature = 'rent:cleanup-duplicates {property_id} {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up duplicate payment transactions for a property';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $dryRun = $this->option('dry-run');

        $property = Property::find($propertyId);

        if (!$property) {
            $this->error("Property #{$propertyId} not found!");
            return 1;
        }

        $this->info("Property: {$property->name}");
        $this->info("Current Balance: \${$property->current_balance}");
        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
            $this->newLine();
        }

        // Find rent checks with duplicate payment transactions
        $rentChecks = RentCheck::where('property_id', $property->id)->get();
        $duplicatesFound = 0;
        $transactionsToDelete = [];

        foreach ($rentChecks as $rentCheck) {
            $paymentTransactions = PropertyTransaction::where('rent_check_id', $rentCheck->id)
                ->whereIn('type', ['rent_payment', 'manual_payment'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($paymentTransactions->count() > 1) {
                $duplicatesFound++;

                $this->warn("\nRent Check #{$rentCheck->id} - Due: {$rentCheck->due_date->format('M j, Y')}");
                $this->line("  Status: {$rentCheck->status}");
                $this->line("  Found {$paymentTransactions->count()} payment transactions:");

                foreach ($paymentTransactions as $index => $transaction) {
                    $this->line(sprintf(
                        "    [%d] ID: %-4d | Date: %s | Type: %-20s | Amount: \$%s | Source: %s | Created: %s",
                        $index + 1,
                        $transaction->id,
                        $transaction->transaction_date->format('Y-m-d'),
                        $transaction->type,
                        number_format($transaction->amount, 2),
                        $transaction->source,
                        $transaction->created_at->format('Y-m-d H:i:s')
                    ));

                    // Keep the first transaction, mark others for deletion
                    if ($index > 0) {
                        $transactionsToDelete[] = $transaction;
                    }
                }

                $this->info("  → Will keep transaction #{$paymentTransactions->first()->id}");
                $this->error("  → Will DELETE " . ($paymentTransactions->count() - 1) . " duplicate(s)");
            }
        }

        if ($duplicatesFound === 0) {
            $this->info("✓ No duplicate payment transactions found!");
            return 0;
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Rent checks with duplicates: {$duplicatesFound}");
        $this->line("  Transactions to delete: " . count($transactionsToDelete));

        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN. Run without --dry-run to actually delete these transactions.");
            return 0;
        }

        $this->newLine();
        if (!$this->confirm('Do you want to delete these duplicate transactions?')) {
            $this->info('Aborted.');
            return 0;
        }

        // Delete duplicates
        DB::beginTransaction();
        try {
            foreach ($transactionsToDelete as $transaction) {
                $this->line("Deleting transaction #{$transaction->id}...");
                $transaction->delete();
            }

            // Recalculate property balance
            $property->updateBalance();
            $property->refresh();

            DB::commit();

            $this->newLine();
            $this->info("✓ Successfully deleted " . count($transactionsToDelete) . " duplicate transactions");
            $this->info("✓ New balance: \${$property->current_balance}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

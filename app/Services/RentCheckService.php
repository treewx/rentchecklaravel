<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\RentCheck;
use App\Models\TenantNotifiable;
use App\Models\User;
use App\Notifications\RentStatusNotification;
use App\Notifications\TenantMissedPaymentNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RentCheckService
{
    private AkahuService $akahuService;

    public function __construct(AkahuService $akahuService)
    {
        $this->akahuService = $akahuService;
    }

    public function checkRentForAllProperties(): array
    {
        $results = [
            'checked' => 0,
            'received' => 0,
            'late' => 0,
            'errors' => []
        ];

        $userNotifications = [];

        // Check both pending checks AND late checks within 7-day grace period
        $pendingChecks = RentCheck::with(['property.user'])
            ->where(function($query) {
                $query->where('status', 'pending')
                      ->orWhere(function($q) {
                          $q->where('status', 'late')
                            ->where('due_date', '>=', now()->subDays(7));
                      });
            })
            ->where('due_date', '<=', now())
            ->get();

        foreach ($pendingChecks as $rentCheck) {
            try {
                $result = $this->checkRentForProperty($rentCheck);
                $results['checked']++;

                $userId = $rentCheck->property->user_id;
                if (!isset($userNotifications[$userId])) {
                    $userNotifications[$userId] = [
                        'user' => $rentCheck->property->user,
                        'results' => [
                            'received' => [],
                            'late' => [],
                            'partial' => []
                        ]
                    ];
                }

                // Add to notification results
                $rentCheck->refresh(); // Refresh to get updated data
                if ($result === 'received') {
                    $results['received']++;
                    $userNotifications[$userId]['results']['received'][] = [
                        'property' => $rentCheck->property,
                        'rent_check' => $rentCheck
                    ];
                } elseif ($result === 'late') {
                    $results['late']++;
                    $userNotifications[$userId]['results']['late'][] = [
                        'property' => $rentCheck->property,
                        'rent_check' => $rentCheck
                    ];
                } elseif ($result === 'partial') {
                    $userNotifications[$userId]['results']['partial'][] = [
                        'property' => $rentCheck->property,
                        'rent_check' => $rentCheck
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Rent check failed for property ' . $rentCheck->property->name . ': ' . $e->getMessage());
                $results['errors'][] = 'Property: ' . $rentCheck->property->name . ' - ' . $e->getMessage();
            }
        }

        $this->createNextRentChecks();

        // Send email notifications to users (property owners)
        $this->sendNotifications($userNotifications);

        // Send email notifications to tenants for missed payments
        $this->sendTenantNotifications($userNotifications);

        return $results;
    }

    public function checkRentForProperty(RentCheck $rentCheck): string
    {
        $property = $rentCheck->property;
        $user = $property->user;

        if (!$user->akahuCredentials) {
            throw new \Exception('No Akahu credentials found for user');
        }

        $startDate = $rentCheck->due_date->copy()->subDays(3);
        $endDate = $rentCheck->due_date->copy()->addDays(5);

        // Get all user accounts and their transactions
        $accounts = $this->akahuService->getAccounts($user);
        $allTransactions = [];

        foreach ($accounts as $account) {
            $accountTransactions = $this->akahuService->getTransactions(
                $user,
                $account['_id'],
                $startDate,
                $endDate
            );
            $allTransactions = array_merge($allTransactions, $accountTransactions);
        }

        $matchingTransactions = $this->findMatchingTransactions(
            $allTransactions,
            $rentCheck->expected_amount,
            $rentCheck->due_date,
            $property->bank_statement_keyword
        );

        $rentCheck->checked_at = now();
        $rentCheck->matching_transactions = $matchingTransactions;

        $previousStatus = $rentCheck->status;

        if (!empty($matchingTransactions)) {
            $totalReceived = collect($matchingTransactions)->sum('amount');

            $rentCheck->received_amount = abs($totalReceived);
            $rentCheck->received_at = now();

            if (abs($totalReceived) >= $rentCheck->expected_amount) {
                $rentCheck->status = 'received';
                $rentCheck->transaction_id = $matchingTransactions[0]['_id'] ?? null;
                $result = 'received';
            } else {
                $rentCheck->status = 'partial';
                $result = 'partial';
            }

            // Create credit transaction for payment received (if not already created)
            // Check for ANY payment transactions (automatic or manual) to avoid duplicates
            $existingTransaction = PropertyTransaction::where('rent_check_id', $rentCheck->id)
                ->whereIn('type', ['rent_payment', 'manual_payment'])
                ->first();

            if (!$existingTransaction) {
                PropertyTransaction::create([
                    'property_id' => $property->id,
                    'rent_check_id' => $rentCheck->id,
                    'transaction_date' => $rentCheck->received_at,
                    'amount' => abs($totalReceived), // Positive = credit
                    'type' => 'rent_payment',
                    'description' => 'Rent payment received for ' . $rentCheck->due_date->format('M j, Y'),
                    'source' => 'akahu',
                    'akahu_transaction_id' => $matchingTransactions[0]['_id'] ?? null,
                ]);

                // Update property balance
                $property->updateBalance();
            } else {
                // Payment transaction already exists (manual or automatic)
                // Just update the rent check status without creating duplicate transaction
                Log::info("Skipping duplicate payment transaction for rent check #{$rentCheck->id} - existing transaction found");
            }
        } else {
            if ($rentCheck->due_date->isPast()) {
                $rentCheck->status = 'late';
                $result = 'late';
            } else {
                $result = 'pending';
            }
        }

        $rentCheck->save();

        return $result;
    }

    private function findMatchingTransactions(array $transactions, float $expectedAmount, Carbon $dueDate, string $keyword): array
    {
        $matchingTransactions = [];
        $tolerance = 0.01;
        $keyword = strtolower(trim($keyword));

        // First pass: Find transactions that match both keyword and amount exactly
        foreach ($transactions as $transaction) {
            $amount = (float) $transaction['amount'];

            // Skip positive amounts (incoming to the account owner, not rent payments)
            if ($amount > 0) {
                continue;
            }

            // Check if transaction description contains the keyword
            $description = strtolower($transaction['description'] ?? '');
            $merchant = strtolower($transaction['merchant']['name'] ?? '');
            $reference = strtolower($transaction['meta']['reference'] ?? '');

            $hasKeyword = str_contains($description, $keyword) ||
                         str_contains($merchant, $keyword) ||
                         str_contains($reference, $keyword);

            if ($hasKeyword && abs(abs($amount) - $expectedAmount) <= $tolerance) {
                $matchingTransactions[] = $transaction;
                break; // Found exact match, stop searching
            }
        }

        // Second pass: If no exact match, find partial amounts with keyword
        if (empty($matchingTransactions)) {
            foreach ($transactions as $transaction) {
                $amount = (float) $transaction['amount'];

                if ($amount > 0) {
                    continue;
                }

                // Check if transaction description contains the keyword
                $description = strtolower($transaction['description'] ?? '');
                $merchant = strtolower($transaction['merchant']['name'] ?? '');
                $reference = strtolower($transaction['meta']['reference'] ?? '');

                $hasKeyword = str_contains($description, $keyword) ||
                             str_contains($merchant, $keyword) ||
                             str_contains($reference, $keyword);

                if ($hasKeyword && abs($amount) >= ($expectedAmount * 0.8)) {
                    $matchingTransactions[] = $transaction;
                }
            }
        }

        // Third pass: If still no matches, try amount-only matching (fallback)
        if (empty($matchingTransactions)) {
            foreach ($transactions as $transaction) {
                $amount = (float) $transaction['amount'];

                if ($amount > 0) {
                    continue;
                }

                if (abs(abs($amount) - $expectedAmount) <= $tolerance) {
                    $matchingTransactions[] = $transaction;
                    break;
                }
            }
        }

        return $matchingTransactions;
    }

    private function createNextRentChecks(): void
    {
        $properties = Property::where('is_active', true)
            ->whereDoesntHave('rentChecks', function ($query) {
                $query->where('due_date', '>', now())
                      ->where('status', 'pending');
            })
            ->get();

        foreach ($properties as $property) {
            $nextDueDate = $this->calculateNextRentDueDate($property);

            $rentCheck = RentCheck::create([
                'property_id' => $property->id,
                'due_date' => $nextDueDate,
                'expected_amount' => $property->rent_amount,
                'status' => 'pending',
            ]);

            // Create debit transaction for rent due
            PropertyTransaction::create([
                'property_id' => $property->id,
                'rent_check_id' => $rentCheck->id,
                'transaction_date' => $nextDueDate,
                'amount' => -$property->rent_amount, // Negative = debit (tenant owes)
                'type' => 'rent_due',
                'description' => 'Rent due for ' . $nextDueDate->format('M j, Y'),
                'source' => 'system',
            ]);

            // Update property balance
            $property->updateBalance();
        }
    }

    private function calculateNextRentDueDate(Property $property): Carbon
    {
        $today = now();
        $frequency = $property->rent_frequency;
        $dayOfWeek = $property->rent_due_day_of_week;

        switch ($frequency) {
            case 'weekly':
                // Find next occurrence of the specified day of week
                $nextDueDate = $today->copy()->next($this->getDayName($dayOfWeek))->setTime(0, 0, 0);
                break;

            case 'fortnightly':
                // Find next occurrence of the specified day of week, then add a week if needed
                $nextDueDate = $today->copy()->next($this->getDayName($dayOfWeek))->setTime(0, 0, 0);
                // For fortnightly, we need to check if there should be a 2-week gap
                // This is a simplified approach - you might want to store last payment date for more accuracy
                if ($nextDueDate->diffInDays($today) < 7) {
                    $nextDueDate->addWeeks(1);
                }
                break;

            case 'monthly':
            default:
                // Find next occurrence of the specified day of week in the next month
                $nextDueDate = $today->copy()->addMonth()->startOfMonth();
                while ($nextDueDate->dayOfWeek !== $dayOfWeek) {
                    $nextDueDate->addDay();
                }
                // If we've passed this week's occurrence, move to next month
                if ($nextDueDate <= $today) {
                    $nextDueDate->addMonth();
                    while ($nextDueDate->dayOfWeek !== $dayOfWeek) {
                        $nextDueDate->addDay();
                    }
                }
                break;
        }

        return $nextDueDate;
    }

    private function getDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        return $days[$dayOfWeek] ?? 'Monday';
    }

    private function sendNotifications(array $userNotifications): void
    {
        foreach ($userNotifications as $userId => $notificationData) {
            $user = $notificationData['user'];
            $results = $notificationData['results'];

            Log::info('Checking notification for user: ' . $user->email, [
                'email_enabled' => $user->email_notifications_enabled,
                'email_on_late' => $user->email_on_rent_late,
                'late_count' => count($results['late'])
            ]);

            // Skip if user has disabled email notifications
            if (!$user->email_notifications_enabled) {
                Log::info('Email notifications disabled for user: ' . $user->email);
                continue;
            }

            // Filter results based on user preferences
            $filteredResults = [
                'received' => $user->email_on_rent_received ? $results['received'] : [],
                'late' => $user->email_on_rent_late ? $results['late'] : [],
                'partial' => $user->email_on_rent_partial ? $results['partial'] : []
            ];

            // Only send notification if there are filtered results to report
            $totalResults = count($filteredResults['received']) + count($filteredResults['late']) + count($filteredResults['partial']);

            Log::info('Notification total results: ' . $totalResults);

            if ($totalResults > 0) {
                try {
                    Log::info('Sending notification to: ' . $user->email);
                    $user->notify(new RentStatusNotification(
                        $filteredResults,
                        now()->format('F j, Y')
                    ));
                    Log::info('Notification sent successfully to: ' . $user->email);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification to: ' . $user->email, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::info('No results to notify for user: ' . $user->email);
            }
        }
    }

    private function sendTenantNotifications(array $userNotifications): void
    {
        foreach ($userNotifications as $userId => $notificationData) {
            $results = $notificationData['results'];

            // Only send notifications to tenants for late and partial payments
            $missedPayments = array_merge($results['late'], $results['partial']);

            foreach ($missedPayments as $paymentData) {
                $property = $paymentData['property'];
                $rentCheck = $paymentData['rent_check'];

                // Check if the property has tenant email and notification is enabled
                if (empty($property->tenant_email) || !$property->notify_on_missed_payment) {
                    Log::info('Skipping tenant notification for property: ' . $property->name, [
                        'has_email' => !empty($property->tenant_email),
                        'notify_enabled' => $property->notify_on_missed_payment
                    ]);
                    continue;
                }

                try {
                    Log::info('Sending missed payment notification to tenant: ' . $property->tenant_email, [
                        'property' => $property->name,
                        'rent_check_id' => $rentCheck->id,
                        'status' => $rentCheck->status
                    ]);

                    // Create a notifiable object for the tenant
                    $tenant = new TenantNotifiable(
                        $property->tenant_email,
                        $property->tenant_name ?? 'Tenant'
                    );

                    // Send the notification
                    $tenant->notify(new TenantMissedPaymentNotification($property, $rentCheck));

                    Log::info('Tenant notification sent successfully to: ' . $property->tenant_email);
                } catch (\Exception $e) {
                    Log::error('Failed to send tenant notification', [
                        'property' => $property->name,
                        'tenant_email' => $property->tenant_email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
    }
}
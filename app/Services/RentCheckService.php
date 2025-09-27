<?php

namespace App\Services;

use App\Models\Property;
use App\Models\RentCheck;
use App\Models\User;
use App\Notifications\RentStatusNotification;
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

        $pendingChecks = RentCheck::with(['property.user'])
            ->where('status', 'pending')
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

        // Send email notifications to users
        $this->sendNotifications($userNotifications);

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

            RentCheck::create([
                'property_id' => $property->id,
                'due_date' => $nextDueDate,
                'expected_amount' => $property->rent_amount,
                'status' => 'pending',
            ]);
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

            // Skip if user has disabled email notifications
            if (!$user->email_notifications_enabled) {
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
            if ($totalResults > 0) {
                $user->notify(new RentStatusNotification(
                    $filteredResults,
                    now()->format('F j, Y')
                ));
            }
        }
    }
}
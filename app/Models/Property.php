<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'rent_amount',
        'current_balance',
        'rent_due_day_of_week',
        'rent_frequency',
        'rent_start_date',
        'tenant_name',
        'tenant_email',
        'notify_on_missed_payment',
        'bank_statement_keyword',
        'is_active',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'notify_on_missed_payment' => 'boolean',
        'rent_due_day_of_week' => 'integer',
        'rent_start_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentChecks(): HasMany
    {
        return $this->hasMany(RentCheck::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PropertyTransaction::class);
    }

    /**
     * Calculate and update the current balance based on all transactions
     */
    public function updateBalance(): void
    {
        $balance = $this->transactions()->sum('amount');
        $this->update(['current_balance' => $balance]);
    }

    /**
     * Get the balance formatted for display
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format(abs($this->current_balance), 2);
    }

    /**
     * Check if property has outstanding balance (tenant owes money)
     */
    public function hasOutstandingBalance(): bool
    {
        return $this->current_balance < 0;
    }

    public function getNextRentDueDateAttribute()
    {
        // Use rent_start_date as base if set, otherwise use today (backward compatibility)
        $baseDate = $this->rent_start_date ? \Carbon\Carbon::parse($this->rent_start_date) : now();
        $today = now();
        $frequency = $this->rent_frequency;
        $dayOfWeek = $this->rent_due_day_of_week;

        // If we have a start date, calculate from there
        if ($this->rent_start_date) {
            $startDate = \Carbon\Carbon::parse($this->rent_start_date)->setTime(0, 0, 0);
            
            // If start date is in the future, use it as the next due date
            if ($startDate->isFuture()) {
                return $startDate;
            }
            
            // Start date is in the past, calculate next occurrence based on frequency
            switch ($frequency) {
                case 'weekly':
                    // Calculate how many complete weeks have passed since start
                    $weeksPassed = $startDate->diffInWeeks($today, false);
                    // Next payment is start date + (weeks passed + 1)
                    $dueDate = $startDate->copy()->addWeeks($weeksPassed + 1);
                    // If the calculated date is today or in the past, move to next week
                    if ($dueDate <= $today) {
                        $dueDate->addWeek();
                    }
                    break;

                case 'fortnightly':
                    // Calculate how many complete fortnights (14 days) have passed
                    $daysPassed = $startDate->diffInDays($today, false);
                    $fortnightsPassed = floor($daysPassed / 14);
                    // Next payment is start date + (fortnights passed + 1) * 2 weeks
                    $dueDate = $startDate->copy()->addWeeks(($fortnightsPassed + 1) * 2);
                    // If the calculated date is today or in the past, move to next fortnight
                    if ($dueDate <= $today) {
                        $dueDate->addWeeks(2);
                    }
                    break;

                case 'monthly':
                default:
                    // Calculate how many complete months have passed since start
                    $monthsPassed = $startDate->diffInMonths($today, false);
                    // Next payment is start date + (months passed + 1) months
                    $dueDate = $startDate->copy()->addMonths($monthsPassed + 1);
                    // If the calculated date is today or in the past, move to next month
                    if ($dueDate <= $today) {
                        $dueDate->addMonth();
                    }
                    // Adjust to match the day of week (find the same day of week in that month)
                    // Start from the beginning of the month and find the matching day of week
                    $dueDate->startOfMonth();
                    while ($dueDate->dayOfWeek !== $dayOfWeek) {
                        $dueDate->addDay();
                    }
                    // If we're still in the past, move to next month
                    if ($dueDate <= $today) {
                        $dueDate->addMonth();
                        $dueDate->startOfMonth();
                        while ($dueDate->dayOfWeek !== $dayOfWeek) {
                            $dueDate->addDay();
                        }
                    }
                    break;
            }
        } else {
            // Backward compatibility: calculate from today (original behavior)
            switch ($frequency) {
                case 'weekly':
                    // Find next occurrence of the specified day of week
                    $dueDate = $today->copy()->next($this->getDayName($dayOfWeek))->setTime(0, 0, 0);
                    break;

                case 'fortnightly':
                    // Find next occurrence of the specified day of week, then add a week if needed
                    $dueDate = $today->copy()->next($this->getDayName($dayOfWeek))->setTime(0, 0, 0);
                    // For fortnightly, we need to check if there should be a 2-week gap
                    // This is a simplified approach - you might want to store last payment date for more accuracy
                    if ($dueDate->diffInDays($today) < 7) {
                        $dueDate->addWeeks(1);
                    }
                    break;

                case 'monthly':
                default:
                    // Find next occurrence of the specified day of week in the next month
                    $dueDate = $today->copy()->addMonth()->startOfMonth();
                    while ($dueDate->dayOfWeek !== $dayOfWeek) {
                        $dueDate->addDay();
                    }
                    // If we've passed this week's occurrence, move to next month
                    if ($dueDate <= $today) {
                        $dueDate->addMonth();
                        while ($dueDate->dayOfWeek !== $dayOfWeek) {
                            $dueDate->addDay();
                        }
                    }
                    break;
            }
        }

        return $dueDate->setTime(0, 0, 0);
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
}
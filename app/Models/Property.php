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
        $today = now();
        $frequency = $this->rent_frequency;
        $dayOfWeek = $this->rent_due_day_of_week;

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

        return $dueDate;
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
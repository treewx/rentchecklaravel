<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentCheck extends Model
{
    protected $fillable = [
        'property_id',
        'due_date',
        'expected_amount',
        'received_amount',
        'status',
        'transaction_id',
        'received_at',
        'checked_at',
        'tenant_notified_at',
        'matching_transactions',
    ];

    protected $casts = [
        'due_date' => 'date',
        'expected_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'checked_at' => 'datetime',
        'tenant_notified_at' => 'datetime',
        'matching_transactions' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The moment this rent check becomes "late" if unpaid: the morning after
     * the due date, once overnight bank settlement and the Akahu sync have
     * had time to complete. Payments dated on the due date arrive during
     * that day, so midnight on the due date is too early to judge.
     */
    public function lateAfter(): \Carbon\Carbon
    {
        return $this->due_date->copy()
            ->addDay()
            ->setTime(config('rent.late_cutoff_hour', 8), 0);
    }

    public function isLate(): bool
    {
        return $this->status === 'pending' && now()->greaterThanOrEqualTo($this->lateAfter());
    }

    public function isPartial(): bool
    {
        return $this->received_amount && $this->received_amount < $this->expected_amount;
    }

    /**
     * Human-friendly, day-based description of when this check is due.
     * Hour-based countdowns ("due in 6 hours") confuse more than they help
     * since due dates are calendar days, not moments.
     */
    public function dueDescription(): string
    {
        return static::describeDueDate($this->due_date);
    }

    public static function describeDueDate(\Carbon\Carbon $dueDate): string
    {
        $days = (int) now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);

        return match (true) {
            $days === 0 => 'due today',
            $days === 1 => 'due tomorrow',
            $days > 1 => "due in {$days} days",
            $days === -1 => '1 day overdue',
            default => abs($days) . ' days overdue',
        };
    }
}
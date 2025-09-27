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
        'matching_transactions',
    ];

    protected $casts = [
        'due_date' => 'date',
        'expected_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'checked_at' => 'datetime',
        'matching_transactions' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function isLate(): bool
    {
        return $this->due_date->isPast() && $this->status === 'pending';
    }

    public function isPartial(): bool
    {
        return $this->received_amount && $this->received_amount < $this->expected_amount;
    }
}
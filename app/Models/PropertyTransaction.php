<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTransaction extends Model
{
    protected $fillable = [
        'property_id',
        'rent_check_id',
        'transaction_date',
        'amount',
        'type',
        'description',
        'source',
        'akahu_transaction_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rentCheck(): BelongsTo
    {
        return $this->belongsTo(RentCheck::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope to get only debits (rent due)
     */
    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    /**
     * Scope to get only credits (payments received)
     */
    public function scopeCredits($query)
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Check if this is a debit transaction
     */
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Check if this is a credit transaction
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }
}

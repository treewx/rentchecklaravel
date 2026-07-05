<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkahuCredential extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'accounts',
        'app_token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accounts' => 'array',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'app_token' => 'encrypted',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'app_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
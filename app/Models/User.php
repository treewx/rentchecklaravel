<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_notifications_enabled',
        'email_on_rent_received',
        'email_on_rent_late',
        'email_on_rent_partial',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_notifications_enabled' => 'boolean',
        'email_on_rent_received' => 'boolean',
        'email_on_rent_late' => 'boolean',
        'email_on_rent_partial' => 'boolean',
    ];

    public function akahuCredentials()
    {
        return $this->hasOne(AkahuCredential::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
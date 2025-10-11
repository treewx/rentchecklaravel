<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

/**
 * A simple notifiable class for sending emails to tenants
 * who are not users in the system
 */
class TenantNotifiable
{
    use Notifiable;

    public string $email;
    public string $name;

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}

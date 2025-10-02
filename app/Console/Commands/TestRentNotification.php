<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\RentStatusNotification;
use Illuminate\Console\Command;

class TestRentNotification extends Command
{
    protected $signature = 'rent:test-notification';

    protected $description = 'Send a test rent notification email';

    public function handle(): int
    {
        $user = User::first();

        if (!$user) {
            $this->error('No users found in database');
            return self::FAILURE;
        }

        // Get the user's first property
        $property = $user->properties()->first();

        if (!$property) {
            $this->error('User has no properties');
            return self::FAILURE;
        }

        // Get or create a rent check
        $rentCheck = $property->rentChecks()->first();

        if (!$rentCheck) {
            $this->error('Property has no rent checks');
            return self::FAILURE;
        }

        $this->info('Sending test notification to: ' . $user->email);

        // Send test notification with real data
        $user->notify(new RentStatusNotification(
            [
                'received' => [],
                'late' => [
                    [
                        'property' => $property,
                        'rent_check' => $rentCheck
                    ]
                ],
                'partial' => []
            ],
            now()->format('F j, Y')
        ));

        $this->info('Test notification sent successfully!');
        return self::SUCCESS;
    }
}

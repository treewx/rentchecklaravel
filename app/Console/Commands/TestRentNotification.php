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

        $this->info('Sending test notification to: ' . $user->email);

        // Send test notification with dummy data
        $user->notify(new RentStatusNotification(
            [
                'received' => [],
                'late' => [
                    [
                        'property' => (object)[
                            'name' => 'Test Property',
                        ],
                        'rent_check' => (object)[
                            'expected_amount' => 500,
                            'due_date' => now()->subDays(2),
                        ]
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

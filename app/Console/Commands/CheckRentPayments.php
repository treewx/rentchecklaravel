<?php

namespace App\Console\Commands;

use App\Services\RentCheckService;
use Illuminate\Console\Command;

class CheckRentPayments extends Command
{
    protected $signature = 'rent:check';

    protected $description = 'Check for rent payments on due dates';

    private RentCheckService $rentCheckService;

    public function __construct(RentCheckService $rentCheckService)
    {
        parent::__construct();
        $this->rentCheckService = $rentCheckService;
    }

    public function handle(): int
    {
        $this->info('Starting rent payment checks...');

        try {
            $results = $this->rentCheckService->checkRentForAllProperties();

            $this->info("Rent check completed:");
            $this->info("- Properties checked: {$results['checked']}");
            $this->info("- Rent received: {$results['received']}");
            $this->info("- Late payments: {$results['late']}");

            if (!empty($results['errors'])) {
                $this->error("Errors occurred:");
                foreach ($results['errors'] as $error) {
                    $this->error("- $error");
                }
                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Rent check failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
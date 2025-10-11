<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\TenantNotifiable;
use App\Notifications\TenantMissedPaymentNotification;
use Illuminate\Console\Command;

class TestTenantNotification extends Command
{
    protected $signature = 'rent:test-tenant-notification {property_id?}';

    protected $description = 'Send a test missed payment notification to a tenant';

    public function handle(): int
    {
        try {
            $propertyId = $this->argument('property_id');

            if ($propertyId) {
                $property = Property::find($propertyId);
            } else {
                $property = Property::whereNotNull('tenant_email')->first();
            }

            if (!$property) {
                $this->error('No property found with a tenant email address');
                return self::FAILURE;
            }

            if (empty($property->tenant_email)) {
                $this->error('Property "' . $property->name . '" has no tenant email address');
                return self::FAILURE;
            }

            // Get or create a rent check for testing
            $rentCheck = $property->rentChecks()->latest('due_date')->first();

            if (!$rentCheck) {
                $this->error('Property "' . $property->name . '" has no rent checks');
                return self::FAILURE;
            }

            $this->info('Sending test notification to tenant');
            $this->info('Property: ' . $property->name);
            $this->info('Tenant Email: ' . $property->tenant_email);
            $this->info('Tenant Name: ' . ($property->tenant_name ?? 'Not set'));
            $this->info('Rent Check Due: ' . $rentCheck->due_date->format('Y-m-d'));
            $this->info('Amount: $' . number_format($rentCheck->expected_amount, 2));
            $this->info('Status: ' . $rentCheck->status);
            $this->info('');
            $this->info('Mail config - From: ' . config('mail.from.address'));
            $this->info('');

            // Create a tenant notifiable object
            $tenant = new TenantNotifiable(
                $property->tenant_email,
                $property->tenant_name ?? 'Tenant'
            );

            // Send test notification
            $tenant->notify(new TenantMissedPaymentNotification($property, $rentCheck));

            $this->info('✓ Test notification sent successfully to: ' . $property->tenant_email);
            $this->info('');
            $this->info('Check the tenant\'s inbox for the email.');
            $this->info('If using Mailtrap, check your Mailtrap inbox.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Test tenant notification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
}

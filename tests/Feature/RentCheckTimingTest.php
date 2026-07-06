<?php

namespace Tests\Feature;

use App\Models\AkahuCredential;
use App\Models\Property;
use App\Models\RentCheck;
use App\Models\User;
use App\Notifications\RentStatusNotification;
use App\Notifications\TenantMissedPaymentNotification;
use App\Services\AkahuService;
use App\Services\RentCheckService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RentCheckTimingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        // Akahu returns one account with no transactions unless overridden
        $this->mock(AkahuService::class, function ($mock) {
            $mock->shouldReceive('getAccounts')->andReturn([['_id' => 'acc_1']]);
            $mock->shouldReceive('getTransactions')->andReturn([]);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeProperty(array $overrides = []): Property
    {
        $user = User::factory()->create();

        AkahuCredential::create([
            'user_id' => $user->id,
            'access_token' => 'token',
            'app_token' => 'app-token',
            'accounts' => [['_id' => 'acc_1']],
        ]);

        return Property::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Property',
            'rent_amount' => 500,
            'rent_due_day_of_week' => 1,
            'rent_frequency' => 'weekly',
            'bank_statement_keyword' => 'rent',
            'is_active' => true,
        ], $overrides));
    }

    private function makeRentCheck(Property $property, string $dueDate, string $status = 'pending'): RentCheck
    {
        return RentCheck::create([
            'property_id' => $property->id,
            'due_date' => $dueDate,
            'expected_amount' => 500,
            'status' => $status,
        ]);
    }

    public function test_late_cutoff_is_the_morning_after_the_due_date(): void
    {
        $property = $this->makeProperty();
        $check = $this->makeRentCheck($property, '2026-07-07');

        $this->assertSame('2026-07-08 08:00:00', $check->lateAfter()->toDateTimeString());
    }

    public function test_rent_due_today_is_not_marked_late_on_the_due_date(): void
    {
        // 5 PM on the due date - payment may still arrive overnight
        Carbon::setTestNow('2026-07-07 17:00:00');

        $property = $this->makeProperty();
        $check = $this->makeRentCheck($property, '2026-07-07');

        $result = app(RentCheckService::class)->checkRentForProperty($check);

        $this->assertSame('pending', $result);
        $this->assertSame('pending', $check->fresh()->status);
    }

    public function test_rent_is_not_late_before_the_morning_cutoff(): void
    {
        // 7 AM the day after - overnight settlement may not have synced yet
        Carbon::setTestNow('2026-07-08 07:00:00');

        $property = $this->makeProperty();
        $check = $this->makeRentCheck($property, '2026-07-07');

        $result = app(RentCheckService::class)->checkRentForProperty($check);

        $this->assertSame('pending', $result);
    }

    public function test_rent_is_late_after_the_morning_cutoff(): void
    {
        // 9 AM the day after the due date - now it's genuinely late
        Carbon::setTestNow('2026-07-08 09:00:00');

        $property = $this->makeProperty();
        $check = $this->makeRentCheck($property, '2026-07-07');

        $result = app(RentCheckService::class)->checkRentForProperty($check);

        $this->assertSame('late', $result);
        $this->assertSame('late', $check->fresh()->status);
    }

    public function test_owner_is_notified_once_not_on_every_recheck(): void
    {
        Carbon::setTestNow('2026-07-08 09:00:00');

        $property = $this->makeProperty();
        $this->makeRentCheck($property, '2026-07-07');

        $service = app(RentCheckService::class);

        // First run: transition to late -> one owner notification
        $service->checkRentForAllProperties();
        Notification::assertSentTimes(RentStatusNotification::class, 1);

        // Second run same day: still late -> no additional notification
        Carbon::setTestNow('2026-07-08 18:00:00');
        $service->checkRentForAllProperties();
        Notification::assertSentTimes(RentStatusNotification::class, 1);
    }

    public function test_tenant_is_notified_once_after_grace_period(): void
    {
        $property = $this->makeProperty([
            'tenant_email' => 'tenant@example.com',
            'tenant_name' => 'Terry Tenant',
            'notify_on_missed_payment' => true,
            'grace_period_days' => 2,
        ]);
        $check = $this->makeRentCheck($property, '2026-07-07');

        $service = app(RentCheckService::class);

        // Day after due date: late, but within the 2-day tenant grace period
        Carbon::setTestNow('2026-07-08 09:00:00');
        $service->checkRentForAllProperties();
        Notification::assertSentTimes(TenantMissedPaymentNotification::class, 0);

        // After the grace period expires: notify the tenant exactly once
        Carbon::setTestNow('2026-07-10 09:00:00');
        $service->checkRentForAllProperties();
        Notification::assertSentTimes(TenantMissedPaymentNotification::class, 1);
        $this->assertNotNull($check->fresh()->tenant_notified_at);

        // Further runs never re-notify
        Carbon::setTestNow('2026-07-11 09:00:00');
        $service->checkRentForAllProperties();
        Notification::assertSentTimes(TenantMissedPaymentNotification::class, 1);
    }

    public function test_due_description_uses_calendar_days(): void
    {
        Carbon::setTestNow('2026-07-06 17:12:00');

        $property = $this->makeProperty();

        $this->assertSame('due tomorrow', $this->makeRentCheck($property, '2026-07-07')->dueDescription());
        $this->assertSame('due today', $this->makeRentCheck($property, '2026-07-06')->dueDescription());
        $this->assertSame('due in 3 days', $this->makeRentCheck($property, '2026-07-09')->dueDescription());
        $this->assertSame('1 day overdue', $this->makeRentCheck($property, '2026-07-05')->dueDescription());
        $this->assertSame('4 days overdue', $this->makeRentCheck($property, '2026-07-02')->dueDescription());
    }
}

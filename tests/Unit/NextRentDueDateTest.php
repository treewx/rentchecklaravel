<?php

namespace Tests\Unit;

use App\Models\Property;
use Carbon\Carbon;
use Tests\TestCase;

class NextRentDueDateTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function property(array $attributes): Property
    {
        return new Property(array_merge([
            'name' => 'Test Property',
            'rent_amount' => 500,
            'rent_frequency' => 'weekly',
            'rent_due_day_of_week' => 1, // Monday
            'bank_statement_keyword' => 'rent',
        ], $attributes));
    }

    public function test_future_start_date_is_used_as_next_due_date(): void
    {
        Carbon::setTestNow('2026-07-06');

        $property = $this->property(['rent_start_date' => '2026-07-20']);

        $this->assertSame('2026-07-20', $property->next_rent_due_date->toDateString());
    }

    public function test_weekly_rent_advances_from_past_start_date(): void
    {
        Carbon::setTestNow('2026-07-06'); // Monday

        // Started Monday 2026-06-01; due date must be a future Monday
        $property = $this->property(['rent_start_date' => '2026-06-01']);
        $dueDate = $property->next_rent_due_date;

        $this->assertTrue($dueDate->isFuture(), "Expected {$dueDate} to be in the future");
        $this->assertSame(Carbon::MONDAY, $dueDate->dayOfWeek);
        // Must stay aligned to the weekly cycle from the start date
        $this->assertSame(0, Carbon::parse('2026-06-01')->diffInDays($dueDate) % 7);
    }

    public function test_fortnightly_rent_stays_on_two_week_cycle(): void
    {
        Carbon::setTestNow('2026-07-06');

        $property = $this->property([
            'rent_frequency' => 'fortnightly',
            'rent_start_date' => '2026-06-01',
        ]);
        $dueDate = $property->next_rent_due_date;

        $this->assertTrue($dueDate->isFuture());
        $this->assertSame(0, Carbon::parse('2026-06-01')->diffInDays($dueDate) % 14);
    }

    public function test_weekly_rent_without_start_date_uses_next_weekday(): void
    {
        Carbon::setTestNow('2026-07-06'); // Monday

        $property = $this->property(['rent_due_day_of_week' => 5]); // Friday
        $dueDate = $property->next_rent_due_date;

        $this->assertSame('2026-07-10', $dueDate->toDateString());
        $this->assertSame(Carbon::FRIDAY, $dueDate->dayOfWeek);
    }

    public function test_monthly_rent_lands_on_configured_day_of_week(): void
    {
        Carbon::setTestNow('2026-07-06');

        $property = $this->property([
            'rent_frequency' => 'monthly',
            'rent_start_date' => '2026-05-04',
            'rent_due_day_of_week' => 1,
        ]);
        $dueDate = $property->next_rent_due_date;

        $this->assertTrue($dueDate->isFuture());
        $this->assertSame(Carbon::MONDAY, $dueDate->dayOfWeek);
    }
}

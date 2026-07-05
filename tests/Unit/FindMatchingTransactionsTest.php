<?php

namespace Tests\Unit;

use App\Services\RentCheckService;
use Carbon\Carbon;
use Tests\TestCase;

class FindMatchingTransactionsTest extends TestCase
{
    private RentCheckService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RentCheckService::class);
    }

    private function match(array $transactions, float $expected = 500.00, string $keyword = 'rent'): array
    {
        return $this->service->findMatchingTransactions(
            $transactions,
            $expected,
            Carbon::parse('2026-07-06'),
            $keyword
        );
    }

    private function transaction(float $amount, string $description = '', array $overrides = []): array
    {
        return array_merge([
            '_id' => 'trans_' . uniqid(),
            'amount' => $amount,
            'description' => $description,
            'date' => '2026-07-06',
        ], $overrides);
    }

    public function test_matches_exact_amount_with_keyword_in_description(): void
    {
        $matches = $this->match([
            $this->transaction(500.00, 'RENT J SMITH'),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_ignores_outgoing_payments_of_same_amount(): void
    {
        $matches = $this->match([
            $this->transaction(-500.00, 'RENT REFUND'),
        ]);

        $this->assertEmpty($matches);
    }

    public function test_keyword_matching_is_case_insensitive(): void
    {
        $matches = $this->match([
            $this->transaction(500.00, 'Weekly Rent Payment'),
        ], 500.00, 'RENT');

        $this->assertCount(1, $matches);
    }

    public function test_matches_keyword_in_merchant_name(): void
    {
        $matches = $this->match([
            $this->transaction(500.00, 'transfer', ['merchant' => ['name' => 'Rent from tenant']]),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_matches_keyword_in_reference(): void
    {
        $matches = $this->match([
            $this->transaction(500.00, 'transfer', ['meta' => ['reference' => 'rent 12 main st']]),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_matches_amount_within_one_cent_tolerance(): void
    {
        $matches = $this->match([
            $this->transaction(500.01, 'RENT J SMITH'),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_partial_payment_with_keyword_matches_at_80_percent(): void
    {
        $matches = $this->match([
            $this->transaction(400.00, 'RENT J SMITH'),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_partial_payment_below_80_percent_does_not_match(): void
    {
        $matches = $this->match([
            $this->transaction(399.99, 'RENT J SMITH'),
        ]);

        $this->assertEmpty($matches);
    }

    public function test_falls_back_to_amount_only_match_without_keyword(): void
    {
        $matches = $this->match([
            $this->transaction(500.00, 'PAYMENT FROM SMITH'),
        ]);

        $this->assertCount(1, $matches);
    }

    public function test_prefers_exact_keyword_match_over_amount_only_fallback(): void
    {
        $unrelated = $this->transaction(500.00, 'SOME OTHER PAYMENT', ['_id' => 'unrelated']);
        $rent = $this->transaction(500.00, 'RENT J SMITH', ['_id' => 'rent']);

        $matches = $this->match([$unrelated, $rent]);

        $this->assertCount(1, $matches);
        $this->assertSame('rent', $matches[0]['_id']);
    }

    public function test_no_transactions_returns_empty(): void
    {
        $this->assertEmpty($this->match([]));
    }
}

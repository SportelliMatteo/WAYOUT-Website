<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrelaunchBenefitApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_request_is_rejected(): void
    {
        $this->getJson('/api/v1/prelaunch/benefits')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_SERVER_SIGNATURE');
    }

    public function test_api_returns_only_verified_waitlist_users_without_successful_founder_purchase(): void
    {
        $eligible = $this->createVerifiedWaitlistEntry('eligible@example.com', 2);
        $buyer = $this->createVerifiedWaitlistEntry('buyer@example.com', 1);
        $pendingPurchase = $this->createVerifiedWaitlistEntry('pending-order@example.com', 3);
        $this->createUnverifiedWaitlistEntry('unverified@example.com');
        $this->createPurchase($buyer, 'succeeded');
        $this->createPurchase($pendingPurchase, 'pending');

        $response = $this->signedGet('/api/v1/prelaunch/benefits');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.benefit_id', $eligible->benefit_id)
            ->assertJsonPath('data.0.email', 'eligible@example.com')
            ->assertJsonPath('data.0.waitlist_position', 2)
            ->assertJsonPath('data.0.benefit_type', 'waitlist')
            ->assertJsonPath('data.0.duration_days', 60)
            ->assertJsonPath('data.1.email', 'pending-order@example.com')
            ->assertJsonMissing(['email' => 'buyer@example.com'])
            ->assertJsonMissing(['email' => 'unverified@example.com']);
    }

    public function test_results_are_paginated_in_waitlist_order(): void
    {
        $this->createVerifiedWaitlistEntry('third@example.com', 3);
        $this->createVerifiedWaitlistEntry('first@example.com', 1);
        $this->createVerifiedWaitlistEntry('second@example.com', 2);

        $response = $this->signedGet('/api/v1/prelaunch/benefits?page=2&per_page=1');

        $response->assertOk()
            ->assertJsonPath('data.0.email', 'second@example.com')
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_backend_can_check_eligibility_by_email(): void
    {
        $entry = $this->createVerifiedWaitlistEntry('eligible@example.com', 12);

        $this->signedPost('/api/v1/prelaunch/benefits/eligibility', [
            'email' => ' Eligible@Example.com ',
        ])->assertOk()
            ->assertJsonPath('data.eligible', true)
            ->assertJsonPath('data.benefit.benefit_id', $entry->benefit_id)
            ->assertJsonPath('data.benefit.email', 'eligible@example.com')
            ->assertJsonPath('data.benefit.waitlist_position', 12)
            ->assertJsonPath('data.benefit.benefit_type', 'waitlist')
            ->assertJsonPath('data.benefit.duration_days', 60);
    }

    public function test_email_lookup_is_negative_for_unknown_unverified_or_founder_buyer(): void
    {
        $buyer = $this->createVerifiedWaitlistEntry('buyer@example.com', 1);
        $this->createPurchase($buyer, 'succeeded');
        $this->createUnverifiedWaitlistEntry('unverified@example.com');

        foreach (['unknown@example.com', 'unverified@example.com', 'buyer@example.com'] as $email) {
            $this->signedPost('/api/v1/prelaunch/benefits/eligibility', [
                'email' => $email,
            ])->assertOk()
                ->assertJsonPath('data.eligible', false)
                ->assertJsonPath('data.benefit', null);
        }
    }

    public function test_nonce_cannot_be_replayed(): void
    {
        $nonce = 'nonce-replay-1234567890';

        $this->signedGet('/api/v1/prelaunch/benefits', $nonce)->assertOk();
        $this->signedGet('/api/v1/prelaunch/benefits', $nonce)
            ->assertConflict()
            ->assertJsonPath('code', 'REPLAY_DETECTED');
    }

    private function createUnverifiedWaitlistEntry(string $email): void
    {
        DB::table('waitlist_entries')->insert([
            'id' => DatabaseUuid::new(),
            'benefit_id' => null,
            'email' => $email,
            'waitlist_position' => null,
            'email_verified_at' => null,
            'marketing_consent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPurchase(object $entry, string $status): void
    {
        DB::table('purchases')->insert([
            'id' => DatabaseUuid::new(),
            'waitlist_entry_id' => $entry->id,
            'email' => $entry->email,
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'status' => $status,
            'invoice_requested' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function signedGet(string $path, ?string $nonce = null)
    {
        $signedPath = parse_url($path, PHP_URL_PATH);
        $headers = $this->benefitApiHeaders('GET', $signedPath, '', $nonce);
        $server = [];

        foreach ($headers as $name => $value) {
            $server[$name === 'Content-Type'
                ? 'CONTENT_TYPE'
                : 'HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('GET', $path, [], [], [], $server, '');
    }

    private function signedPost(string $path, array $payload)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $headers = $this->benefitApiHeaders('POST', $path, $body);
        $server = [];

        foreach ($headers as $name => $value) {
            $server[$name === 'Content-Type'
                ? 'CONTENT_TYPE'
                : 'HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('POST', $path, [], [], [], $server, $body);
    }
}

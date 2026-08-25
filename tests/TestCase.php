<?php

namespace Tests;

use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.stripe.direct_checkout_enabled_for_tests', true);
        config()->set('benefits.server_auth.key', 'test-benefit-key');
        config()->set('benefits.server_auth.secret', 'test-benefit-secret');
    }

    protected function createVerifiedWaitlistEntry(string $email = 'member@example.com', int $position = 1): object
    {
        $id = DatabaseUuid::new();
        DB::table('waitlist_entries')->insert([
            'id' => $id,
            'benefit_id' => DatabaseUuid::new(),
            'email' => strtolower($email),
            'waitlist_position' => $position,
            'email_verified_at' => now(),
            'marketing_consent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('waitlist_entries')->where('id', $id)->first();
    }

    /** @return array<string, string> */
    protected function benefitApiHeaders(string $method, string $path, string $body, ?string $nonce = null): array
    {
        $timestamp = (string) time();
        $nonce ??= 'nonce-'.bin2hex(random_bytes(12));
        $canonical = implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($method),
            '/'.ltrim($path, '/'),
            hash('sha256', $body),
        ]);

        return [
            'X-Wayout-Key' => 'test-benefit-key',
            'X-Wayout-Timestamp' => $timestamp,
            'X-Wayout-Nonce' => $nonce,
            'X-Wayout-Signature' => hash_hmac('sha256', $canonical, 'test-benefit-secret'),
            'Content-Type' => 'application/json',
        ];
    }
}

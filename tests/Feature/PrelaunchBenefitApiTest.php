<?php

namespace Tests\Feature;

use App\Mail\BenefitEmailCodeMail;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PrelaunchBenefitApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_request_is_rejected(): void
    {
        $this->postJson('/api/v1/prelaunch/benefits/claim', [
            'benefit_id' => DatabaseUuid::new(),
            'account_reference' => 'app-user-1',
            'idempotency_key' => 'claim-request-1',
        ])->assertUnauthorized()->assertJsonPath('code', 'INVALID_SERVER_SIGNATURE');
    }

    public function test_backend_can_claim_founder_benefit_idempotently(): void
    {
        $entry = $this->createVerifiedWaitlistEntry();
        DB::table('purchases')->insert([
            'id' => DatabaseUuid::new(),
            'waitlist_entry_id' => $entry->id,
            'email' => $entry->email,
            'plan' => 'creator',
            'amount' => 5900,
            'currency' => 'eur',
            'status' => 'succeeded',
            'invoice_requested' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $payload = [
            'benefit_id' => $entry->benefit_id,
            'account_reference' => 'app-user-1',
            'idempotency_key' => 'claim-request-1',
        ];

        $response = $this->signedJson('/api/v1/prelaunch/benefits/claim', $payload);
        $response->assertOk()
            ->assertJsonPath('data.benefit_type', 'founder_creator')
            ->assertJsonPath('data.waitlist_position', 1);

        $this->assertDatabaseHas('benefit_claims', [
            'waitlist_entry_id' => $entry->id,
            'account_reference' => 'app-user-1',
            'benefit_type' => 'founder_creator',
        ]);
        $this->signedJson('/api/v1/prelaunch/benefits/claim', $payload)->assertOk();
        $this->assertSame(1, DB::table('benefit_claims')->count());
    }

    public function test_claim_cannot_be_moved_to_another_account(): void
    {
        $entry = $this->createVerifiedWaitlistEntry();
        $this->signedJson('/api/v1/prelaunch/benefits/claim', [
            'benefit_id' => $entry->benefit_id,
            'account_reference' => 'app-user-1',
            'idempotency_key' => 'claim-request-1',
        ])->assertOk();

        $this->signedJson('/api/v1/prelaunch/benefits/claim', [
            'benefit_id' => $entry->benefit_id,
            'account_reference' => 'app-user-2',
            'idempotency_key' => 'claim-request-2',
        ])->assertConflict()->assertJsonPath('code', 'BENEFIT_ALREADY_CLAIMED');
    }

    public function test_email_challenge_is_neutral_and_valid_code_claims_benefit(): void
    {
        Mail::fake();
        $entry = $this->createVerifiedWaitlistEntry('recover@example.com');
        $challengeResponse = $this->signedJson('/api/v1/prelaunch/benefits/email-challenges', [
            'email' => 'recover@example.com',
        ]);
        $challengeResponse->assertStatus(202);
        $challengeId = $challengeResponse->json('data.challenge_id');
        $mail = Mail::sent(BenefitEmailCodeMail::class)->first();

        $this->signedJson('/api/v1/prelaunch/benefits/email-challenges/verify', [
            'challenge_id' => $challengeId,
            'code' => $mail->code,
            'account_reference' => 'app-user-recovery',
            'idempotency_key' => 'recovery-request-1',
        ])->assertOk()->assertJsonPath('data.benefit_id', $entry->benefit_id);
    }

    public function test_unknown_email_returns_same_challenge_shape_without_sending_mail(): void
    {
        Mail::fake();
        $this->signedJson('/api/v1/prelaunch/benefits/email-challenges', [
            'email' => 'unknown@example.com',
        ])->assertStatus(202)
            ->assertJsonStructure(['success', 'data' => ['challenge_id', 'expires_in_seconds']]);
        Mail::assertNothingSent();
    }

    private function signedJson(string $path, array $payload)
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

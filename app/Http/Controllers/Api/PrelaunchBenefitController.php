<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BenefitEmailCodeMail;
use App\Support\BenefitClaimConflict;
use App\Support\BenefitService;
use App\Support\DatabaseUuid;
use App\Support\PrivacySafeLogContext;
use App\Support\TransactionalEmailSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class PrelaunchBenefitController extends Controller
{
    public function claim(Request $request, BenefitService $benefits)
    {
        $validated = $request->validate([
            'benefit_id' => ['required', 'uuid'],
            'account_reference' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $entry = DB::table('waitlist_entries')
            ->where('benefit_id', $validated['benefit_id'])
            ->whereNotNull('email_verified_at')
            ->first();

        if (! $entry) {
            return $this->error('BENEFIT_NOT_FOUND', 'Benefit not found.', 404);
        }

        return $this->claimResponse($benefits, $entry, $validated['account_reference'], $validated['idempotency_key']);
    }

    public function createEmailChallenge(Request $request, TransactionalEmailSender $emailSender)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $email = Str::lower(trim($validated['email']));
        $emailHash = hash('sha256', $email);
        $expiresInMinutes = max(1, (int) config('benefits.email_challenge_minutes', 10));
        $cooldown = max(1, (int) config('benefits.email_challenge_resend_seconds', 60));
        $recent = DB::table('benefit_email_challenges')
            ->where('email_hash', $emailHash)
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->latest('created_at')
            ->first();

        if ($recent) {
            return response()->json([
                'success' => true,
                'data' => [
                    'challenge_id' => $recent->id,
                    'expires_in_seconds' => max(1, now()->diffInSeconds(Carbon::parse($recent->expires_at), false)),
                ],
            ], 202);
        }

        $entry = DB::table('waitlist_entries')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNotNull('email_verified_at')
            ->first();
        $challengeId = DatabaseUuid::new();
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes($expiresInMinutes);

        DB::table('benefit_email_challenges')->insert([
            'id' => $challengeId,
            'waitlist_entry_id' => $entry?->id,
            'email_hash' => $emailHash,
            'code_hash' => $this->codeHash($challengeId, $code),
            'attempts' => 0,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        if ($entry) {
            try {
                $emailSender->send($email, new BenefitEmailCodeMail($code, $expiresInMinutes));
            } catch (Throwable $exception) {
                Log::error('Benefit email challenge delivery failed.', [
                    'email_hash' => PrivacySafeLogContext::fingerprint($email),
                    ...PrivacySafeLogContext::exception($exception),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'challenge_id' => $challengeId,
                'expires_in_seconds' => $expiresInMinutes * 60,
            ],
        ], 202);
    }

    public function verifyEmailChallenge(Request $request, BenefitService $benefits)
    {
        $validated = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'code' => ['required', 'digits:6'],
            'account_reference' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated): array {
            $challenge = DB::table('benefit_email_challenges')
                ->where('id', $validated['challenge_id'])
                ->lockForUpdate()
                ->first();
            $maxAttempts = max(1, (int) config('benefits.email_challenge_max_attempts', 5));
            $valid = $challenge
                && ! $challenge->consumed_at
                && now()->lt($challenge->expires_at)
                && (int) $challenge->attempts < $maxAttempts
                && $challenge->waitlist_entry_id
                && hash_equals($challenge->code_hash, $this->codeHash($challenge->id, $validated['code']));

            if (! $valid) {
                if ($challenge && ! $challenge->consumed_at && (int) $challenge->attempts < $maxAttempts) {
                    DB::table('benefit_email_challenges')->where('id', $challenge->id)->increment('attempts');
                }

                return ['valid' => false];
            }

            DB::table('benefit_email_challenges')->where('id', $challenge->id)->update([
                'consumed_at' => now(),
            ]);

            return ['valid' => true, 'waitlist_entry_id' => $challenge->waitlist_entry_id];
        });

        if (! $result['valid']) {
            return $this->error('INVALID_OR_EXPIRED_CODE', 'The verification code is invalid or expired.', 422);
        }

        $entry = DB::table('waitlist_entries')->where('id', $result['waitlist_entry_id'])->first();

        return $this->claimResponse($benefits, $entry, $validated['account_reference'], $validated['idempotency_key']);
    }

    private function claimResponse(BenefitService $benefits, object $entry, string $accountReference, string $idempotencyKey)
    {
        try {
            $benefit = $benefits->claim($entry, $accountReference, $idempotencyKey);
        } catch (BenefitClaimConflict) {
            return $this->error('BENEFIT_ALREADY_CLAIMED', 'Benefit already linked to another account.', 409);
        }

        return response()->json(['success' => true, 'data' => $benefit]);
    }

    private function codeHash(string $challengeId, string $code): string
    {
        return hash_hmac('sha256', $challengeId.'|'.$code, (string) config('app.key'));
    }

    private function error(string $code, string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
        ], $status);
    }
}

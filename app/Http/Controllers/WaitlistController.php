<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistVerificationMail;
use App\Mail\WaitlistWelcomeMail;
use App\Support\ConsentAuditService;
use App\Support\DatabaseUuid;
use App\Support\MetaConversions;
use App\Support\PrivacySafeLogContext;
use App\Support\TransactionalEmailSender;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class WaitlistController extends Controller
{
    public function store(Request $request, TransactionalEmailSender $emailSender, ConsentAuditService $audit)
    {
        if (filled($request->input('website'))) {
            Log::warning('Waitlist honeypot triggered.', [
                'ip_hash' => PrivacySafeLogContext::fingerprint($request->ip()),
                'user_agent_hash' => PrivacySafeLogContext::fingerprint($request->userAgent()),
            ]);

            return back()->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'waitlist_terms_accepted' => ['required', 'accepted'],
            'marketing_consent' => ['nullable', 'boolean'],
        ]);
        $email = Str::lower(trim($validated['email']));
        $marketingConsent = $request->boolean('marketing_consent');

        try {
            $entry = DB::transaction(function () use ($request, $email, $marketingConsent, $audit): object {
                $entry = DB::table('waitlist_entries')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->lockForUpdate()
                    ->first();

                if ($entry) {
                    DB::table('waitlist_entries')->where('id', $entry->id)->update([
                        'email' => $email,
                        'marketing_consent' => $marketingConsent,
                        'updated_at' => now(),
                    ]);
                    $entryId = $entry->id;
                } else {
                    $entryId = DatabaseUuid::new();
                    DB::table('waitlist_entries')->insert([
                        'id' => $entryId,
                        'benefit_id' => DatabaseUuid::new(),
                        'email' => $email,
                        'marketing_consent' => $marketingConsent,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $audit->record(
                    $request,
                    $email,
                    'waitlist_legal',
                    'granted',
                    'waitlist_email_form',
                    ['privacy', 'terms', 'waitlist_acceptance'],
                    ['waitlist_entry_id' => $entryId],
                );

                $latestMarketing = $audit->latestMarketingEvent($entryId);
                if ($marketingConsent && $latestMarketing?->action !== 'granted') {
                    $audit->record($request, $email, 'marketing', 'granted', 'waitlist_email_form',
                        ['marketing', 'privacy'], ['waitlist_entry_id' => $entryId]);
                } elseif (! $marketingConsent && $latestMarketing?->action === 'granted') {
                    $audit->record($request, $email, 'marketing', 'revoked', 'waitlist_email_form',
                        ['marketing', 'privacy'], ['waitlist_entry_id' => $entryId],
                        revokesEventId: $latestMarketing->id);
                }

                return DB::table('waitlist_entries')->where('id', $entryId)->first();
            });

            $verifiedSessionEntryId = $request->session()->get('waitlist_verified_entry_id');
            if ($entry->email_verified_at
                && is_string($verifiedSessionEntryId)
                && hash_equals((string) $entry->id, $verifiedSessionEntryId)) {
                return $this->redirectToOffer($request, $entry, alreadyRegistered: true);
            }

            $recentVerification = DB::table('waitlist_email_verifications')
                ->where('waitlist_entry_id', $entry->id)
                ->whereNull('consumed_at')
                ->where('created_at', '>=', now()->subSeconds(max(1, (int) config('benefits.verification_resend_seconds', 60))))
                ->exists();

            if ($recentVerification) {
                return back()->with('waitlist_verification_sent', true);
            }

            $rawToken = Str::random(64);
            DB::transaction(function () use ($entry, $rawToken): void {
                DB::table('waitlist_email_verifications')
                    ->where('waitlist_entry_id', $entry->id)
                    ->whereNull('consumed_at')
                    ->update(['consumed_at' => now()]);
                DB::table('waitlist_email_verifications')->insert([
                    'id' => DatabaseUuid::new(),
                    'waitlist_entry_id' => $entry->id,
                    'token_hash' => hash('sha256', $rawToken),
                    'expires_at' => now()->addMinutes(max(1, (int) config('benefits.magic_link_minutes', 30))),
                    'created_at' => now(),
                ]);
            });

            if (! $emailSender->send($email, new WaitlistVerificationMail(
                route('waitlist.verify.show', ['token' => $rawToken]),
            ))) {
                return back()->with('waitlist_error', __('messages.messages.email_sending_disabled'));
            }
        } catch (QueryException $exception) {
            Log::error('Waitlist email signup failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return back()->withInput()->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        } catch (Throwable $exception) {
            Log::error('Waitlist verification email failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return back()->withInput()->with('waitlist_error', __('messages.messages.waitlist_verification_email_failed'));
        }

        return back()->with('waitlist_verification_sent', true);
    }

    public function showVerification(string $token)
    {
        return view('pages.waitlist.verify', [
            'token' => $token,
            'verificationValid' => (bool) $this->verificationFor($token),
        ]);
    }

    public function verify(Request $request, string $token, TransactionalEmailSender $emailSender)
    {
        try {
            $result = DB::transaction(function () use ($token): array {
                $verification = DB::table('waitlist_email_verifications')
                    ->where('token_hash', hash('sha256', $token))
                    ->lockForUpdate()
                    ->first();

                if (! $verification || $verification->consumed_at || now()->gte($verification->expires_at)) {
                    return ['valid' => false];
                }

                $entry = DB::table('waitlist_entries')->where('id', $verification->waitlist_entry_id)->lockForUpdate()->first();
                if (! $entry) {
                    return ['valid' => false];
                }

                if (! $entry->email_verified_at) {
                    $capacity = (int) (DB::table('founder_settings')->where('key', 'waitlist_capacity')
                        ->lockForUpdate()->value('value') ?? config('founder.default_capacities.waitlist_capacity'));
                    $verifiedCount = DB::table('waitlist_entries')->whereNotNull('email_verified_at')->count();

                    if ($capacity <= 0 || $verifiedCount >= $capacity) {
                        return ['valid' => true, 'full' => true];
                    }

                    DB::table('waitlist_entries')->where('id', $entry->id)->update([
                        'waitlist_position' => ((int) DB::table('waitlist_entries')->max('waitlist_position')) + 1,
                        'email_verified_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('waitlist_email_verifications')->where('id', $verification->id)->update(['consumed_at' => now()]);

                return ['valid' => true, 'full' => false, 'entry_id' => $entry->id];
            });
        } catch (QueryException $exception) {
            Log::error('Waitlist email verification failed.', PrivacySafeLogContext::exception($exception));

            return redirect()->route('home')->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        if (! $result['valid']) {
            return redirect()->route('home')->with('waitlist_error', __('messages.messages.waitlist_verification_invalid'));
        }
        if ($result['full']) {
            return redirect()->route('home')->with('waitlist_error', __('messages.messages.waitlist_closed'));
        }

        $entry = DB::table('waitlist_entries')->where('id', $result['entry_id'])->first();
        app(MetaConversions::class)->lead($request, $entry);
        $this->sendWelcomeIfNeeded($entry, $emailSender);

        $request->session()->regenerate();

        return $this->redirectToOffer($request, $entry, alreadyRegistered: false);
    }

    private function verificationFor(string $token): ?object
    {
        return DB::table('waitlist_email_verifications')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    private function redirectToOffer(Request $request, object $entry, bool $alreadyRegistered)
    {
        $purchase = DB::table('purchases')
            ->where('waitlist_entry_id', $entry->id)
            ->where('status', 'succeeded')
            ->latest('created_at')
            ->first();

        $request->session()->put('waitlist_verified_entry_id', $entry->id);
        $request->session()->put('waitlist_email', $entry->email);

        return redirect()->route('home')
            ->with('waitlist_offer', true)
            ->with('waitlist_status', ($alreadyRegistered || $purchase) ? 'already_registered' : 'registered')
            ->with('waitlist_email', $entry->email)
            ->with('purchased_plan', $purchase ? [
                'code' => $purchase->plan,
                'name' => $this->planName($purchase->plan),
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
            ] : null);
    }

    private function sendWelcomeIfNeeded(object $entry, TransactionalEmailSender $emailSender): void
    {
        if ($entry->welcome_email_sent_at) {
            return;
        }

        try {
            if ($emailSender->send($entry->email, new WaitlistWelcomeMail([
                'email' => $entry->email,
                'waitlist_position' => (int) $entry->waitlist_position,
                'marketing_revocation_url' => $entry->marketing_consent
                    ? URL::signedRoute('consent.marketing.revoke.show', ['waitlist' => $entry->id])
                    : null,
            ]))) {
                DB::table('waitlist_entries')->where('id', $entry->id)
                    ->whereNull('welcome_email_sent_at')->update(['welcome_email_sent_at' => now()]);
            }
        } catch (Throwable $exception) {
            Log::error('Waitlist welcome email failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($entry->email),
                ...PrivacySafeLogContext::exception($exception),
            ]);
        }
    }

    private function planName(string $plan): string
    {
        return $plan === 'creator' ? 'Founder 12M Creator Pass' : 'Founder Join 12M Pass';
    }
}

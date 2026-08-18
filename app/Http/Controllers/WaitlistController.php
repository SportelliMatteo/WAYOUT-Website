<?php

namespace App\Http\Controllers;

use App\Contracts\PhoneVerificationService;
use App\Exceptions\PhoneVerificationException;
use App\Mail\WaitlistWelcomeMail;
use App\Support\ConsentAuditService;
use App\Support\TransactionalEmailSender;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WaitlistController extends Controller
{
    public function store(Request $request, TransactionalEmailSender $emailSender, ConsentAuditService $audit)
    {
        if (filled($request->input('website'))) {
            Log::warning('Waitlist honeypot triggered.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->withInput($request->except('website'))
                ->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = Str::lower($validated['email']);
        $request->session()->forget('purchase_confirmation_email');

        try {
            $alreadyRegistered = DB::table('waitlist_entries')
                ->where('email', $email)
                ->exists();

            if ($alreadyRegistered) {
                DB::table('waitlist_entries')
                    ->where('email', $email)
                    ->update([
                        'offer_shown' => true,
                        'updated_at' => now(),
                    ]);
            } else {
                $waitlistFull = false;

                DB::transaction(function () use ($email, &$waitlistFull) {
                    $capacity = (int) (DB::table('founder_settings')
                        ->where('key', 'waitlist_capacity')
                        ->lockForUpdate()
                        ->value('value') ?? config('founder.default_capacities.waitlist_capacity'));

                    if ($capacity <= 0 || DB::table('waitlist_entries')->count() >= $capacity) {
                        $waitlistFull = true;

                        return;
                    }

                    DB::table('waitlist_entries')->insert([
                        'email' => $email,
                        'offer_shown' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                });

                if ($waitlistFull) {
                    return back()->withInput($request->except('website'))
                        ->with('waitlist_error', __('messages.messages.waitlist_closed'));
                }
            }

            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
                ->first();

            $waitlistEntry = DB::table('waitlist_entries')
                ->where('email', $email)
                ->first();
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                DB::table('waitlist_entries')
                    ->where('email', $email)
                    ->update([
                        'offer_shown' => true,
                        'updated_at' => now(),
                    ]);

                $alreadyRegistered = true;
                $purchase = DB::table('purchases')
                    ->where('email', $email)
                    ->where('status', 'succeeded')
                    ->latest('created_at')
                    ->first();
                $waitlistEntry = DB::table('waitlist_entries')
                    ->where('email', $email)
                    ->first();
            } else {
                Log::error('Waitlist signup failed.', [
                    'email' => $email,
                    'exception' => $exception,
                ]);

                return back()->withInput($request->except('website'))
                    ->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
            }
        }

        $profileRequired = ! $purchase && (
            ! $this->profileComplete($waitlistEntry ?? null)
            || ! $audit->hasWaitlistLegalAcceptance($waitlistEntry?->id)
        );
        $waitlistStatus = $profileRequired ? 'registered' : ($alreadyRegistered ? 'already_registered' : 'registered');

        if (! $purchase && ! $profileRequired) {
            $this->sendWelcomeIfNeeded($waitlistEntry, $email, [
                'first_name' => $waitlistEntry->first_name,
            ], $emailSender);
        }

        $this->syncPurchaseConfirmationEmail($request, $email, $purchase);

        return back()
            ->with('waitlist_profile_prompt', $profileRequired)
            ->with('waitlist_offer', ! $profileRequired)
            ->with('waitlist_status', $waitlistStatus)
            ->with('waitlist_email', $email)
            ->with('waitlist_profile', $this->profileData($waitlistEntry ?? null))
            ->with('waitlist_profile_required', $profileRequired)
            ->with('purchased_plan', $purchase ? [
                'code' => $purchase->plan,
                'name' => $this->planName($purchase->plan),
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
            ] : null);
    }

    public function completeProfile(
        Request $request,
        TransactionalEmailSender $emailSender,
        ConsentAuditService $audit,
        PhoneVerificationService $phoneVerification,
    ) {
        $phoneVerificationEnabled = (bool) config('services.firebase.phone_verification_enabled', false);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'phone_prefix' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^[0-9\s().-]{5,32}$/'],
            'firebase_id_token' => [Rule::requiredIf($phoneVerificationEnabled), 'nullable', 'string', 'max:10000'],
            'marketing_consent' => ['nullable', 'boolean'],
        ]);

        $email = Str::lower((string) $request->input('email'));
        $status = 'registered';

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_status', $status)
                ->with('waitlist_email', $email)
                ->with('waitlist_profile', [
                    'first_name' => $request->input('first_name', ''),
                    'last_name' => $request->input('last_name', ''),
                    'birth_date' => $request->input('birth_date', ''),
                    'phone_prefix' => $request->input('phone_prefix', '+39'),
                    'phone_number' => $request->input('phone_number', ''),
                    'marketing_consent' => $request->boolean('marketing_consent'),
                ]);
        }

        $validated = $validator->validated();
        $email = Str::lower($validated['email']);
        $nationalPhoneNumber = preg_replace('/\D+/', '', $validated['phone_number']) ?? '';
        $e164PhoneNumber = $validated['phone_prefix'].$nationalPhoneNumber;

        if (! preg_match('/^\+[1-9]\d{6,14}$/', $e164PhoneNumber)) {
            return back()
                ->withErrors(['phone_number' => __('messages.messages.phone_invalid')])
                ->withInput($request->except('firebase_id_token'))
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_status', $status)
                ->with('waitlist_email', $email)
                ->with('waitlist_profile', [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'birth_date' => $validated['birth_date'],
                    'phone_prefix' => $validated['phone_prefix'],
                    'phone_number' => $nationalPhoneNumber,
                    'marketing_consent' => $request->boolean('marketing_consent'),
                ]);
        }

        $profile = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
            'phone_prefix' => $validated['phone_prefix'],
            'phone_number' => $nationalPhoneNumber,
            'marketing_consent' => $request->boolean('marketing_consent'),
        ];

        $verifiedPhone = null;

        if ($phoneVerificationEnabled) {
            try {
                $verifiedPhone = $phoneVerification->verify($validated['firebase_id_token'], $e164PhoneNumber);
            } catch (PhoneVerificationException $exception) {
                Log::warning('Waitlist phone verification failed.', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'reason' => $exception->getMessage(),
                ]);

                return back()
                    ->withErrors(['firebase_id_token' => __('messages.messages.phone_verification_failed')])
                    ->withInput($request->except('firebase_id_token'))
                    ->with('waitlist_profile_prompt', true)
                    ->with('waitlist_status', $status)
                    ->with('waitlist_email', $email)
                    ->with('waitlist_profile', $profile);
            }
        }

        try {
            $waitlistEntry = DB::table('waitlist_entries')->where('email', $email)->first();

            if (! $waitlistEntry) {
                return back()
                    ->withInput()
                    ->with('waitlist_profile_prompt', true)
                    ->with('waitlist_error', __('messages.messages.waitlist_entry_missing'))
                    ->with('waitlist_status', $status)
                    ->with('waitlist_email', $email)
                    ->with('waitlist_profile', $profile);
            }

            DB::transaction(function () use ($request, $email, $profile, $waitlistEntry, $audit, $verifiedPhone) {
                $phoneVerificationData = $verifiedPhone ? [
                    'firebase_uid' => $verifiedPhone->uid,
                    'phone_verified_at' => now(),
                ] : [];

                DB::table('waitlist_entries')
                    ->where('email', $email)
                    ->update([
                        ...$profile,
                        ...$phoneVerificationData,
                        'updated_at' => now(),
                    ]);

                $audit->record(
                    $request,
                    $email,
                    'waitlist_legal',
                    'granted',
                    'waitlist_profile',
                    ['privacy', 'terms', 'waitlist_acceptance'],
                    ['waitlist_entry_id' => $waitlistEntry->id],
                );

                $latestMarketing = $audit->latestMarketingEvent($waitlistEntry->id);
                $marketingGranted = $profile['marketing_consent'];

                if ($marketingGranted && $latestMarketing?->action !== 'granted') {
                    $audit->record(
                        $request,
                        $email,
                        'marketing',
                        'granted',
                        'waitlist_profile',
                        ['marketing', 'privacy'],
                        ['waitlist_entry_id' => $waitlistEntry->id],
                    );
                } elseif (! $marketingGranted && ($waitlistEntry->marketing_consent || $latestMarketing?->action === 'granted')) {
                    $audit->record(
                        $request,
                        $email,
                        'marketing',
                        'revoked',
                        'waitlist_profile',
                        ['marketing', 'privacy'],
                        ['waitlist_entry_id' => $waitlistEntry->id],
                        revokesEventId: $latestMarketing?->action === 'granted' ? $latestMarketing->id : null,
                    );
                }
            });

            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
                ->first();

            $waitlistEntry = DB::table('waitlist_entries')
                ->where('email', $email)
                ->first();
        } catch (QueryException $exception) {
            Log::error('Waitlist profile completion failed.', [
                'email' => $email,
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_error', __('messages.messages.waitlist_generic_error'))
                ->with('waitlist_status', $status)
                ->with('waitlist_email', $email)
                ->with('waitlist_profile', $profile);
        }

        $this->sendWelcomeIfNeeded($waitlistEntry, $email, $profile, $emailSender);
        $this->syncPurchaseConfirmationEmail($request, $email, $purchase);

        return back()
            ->with('waitlist_offer', true)
            ->with('waitlist_status', $status)
            ->with('waitlist_email', $email)
            ->with('waitlist_profile', $profile)
            ->with('purchased_plan', $purchase ? [
                'code' => $purchase->plan,
                'name' => $this->planName($purchase->plan),
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
            ] : null);
    }

    private function syncPurchaseConfirmationEmail(Request $request, string $email, ?object $purchase): void
    {
        if ($purchase) {
            $request->session()->put('purchase_confirmation_email', $email);

            return;
        }

        $request->session()->forget('purchase_confirmation_email');
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array($exception->getCode(), ['23000', '23505'], true);
    }

    /** @param array{first_name: string} $profile */
    private function sendWelcomeIfNeeded(
        ?object $entry,
        string $email,
        array $profile,
        TransactionalEmailSender $emailSender,
    ): void {
        if (! $entry || $entry->welcome_email_sent_at) {
            return;
        }

        try {
            if ($emailSender->send($email, new WaitlistWelcomeMail([
                'email' => $email,
                'first_name' => $profile['first_name'],
                'marketing_revocation_url' => $entry->marketing_consent
                    ? URL::signedRoute('consent.marketing.revoke.show', ['waitlist' => $entry->id])
                    : null,
            ]))) {
                DB::table('waitlist_entries')
                    ->where('id', $entry->id)
                    ->whereNull('welcome_email_sent_at')
                    ->update(['welcome_email_sent_at' => now()]);
            }
        } catch (Throwable $exception) {
            Log::error('Waitlist welcome email failed.', [
                'email' => $email,
                'exception' => $exception,
            ]);
        }
    }

    private function planName(string $plan): string
    {
        return match ($plan) {
            'creator' => 'Founder 12M Creator Pass',
            default => 'Founder Join 12M Pass',
        };
    }

    private function profileData(?object $entry): array
    {
        return [
            'first_name' => $entry->first_name ?? '',
            'last_name' => $entry->last_name ?? '',
            'birth_date' => $entry->birth_date ?? '',
            'phone_prefix' => $entry->phone_prefix ?? '+39',
            'phone_number' => $entry->phone_number ?? '',
            'marketing_consent' => (bool) ($entry->marketing_consent ?? false),
        ];
    }

    private function profileComplete(?object $entry): bool
    {
        return filled($entry?->first_name)
            && filled($entry?->last_name)
            && filled($entry?->birth_date)
            && filled($entry?->phone_prefix)
            && filled($entry?->phone_number)
            && (
                ! config('services.firebase.phone_verification_enabled', false)
                || filled($entry?->phone_verified_at)
            );
    }
}

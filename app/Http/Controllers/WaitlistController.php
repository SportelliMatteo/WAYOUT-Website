<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutRegistrationException;
use App\Mail\WaitlistWelcomeMail;
use App\Support\ConsentAuditService;
use App\Support\PrivacySafeLogContext;
use App\Support\ProfileNicknameGenerator;
use App\Support\TransactionalEmailSender;
use App\Support\WayoutAppRegistrationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class WaitlistController extends Controller
{
    private const REGISTRATION_SESSION_KEY = 'waitlist_phone_registration';

    public function store(
        Request $request,
        TransactionalEmailSender $emailSender,
        ConsentAuditService $audit,
    ) {
        if (filled($request->input('website'))) {
            Log::warning('Waitlist honeypot triggered.', [
                'ip_hash' => PrivacySafeLogContext::fingerprint($request->ip()),
                'user_agent_hash' => PrivacySafeLogContext::fingerprint($request->userAgent()),
            ]);

            return back()->withInput($request->except('website'))
                ->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        $phoneVerificationEnabled = (bool) config('services.firebase.phone_verification_enabled', false);
        $validated = $request->validate([
            'phone_prefix' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^[0-9\s().-]{5,32}$/'],
            'firebase_id_token' => [$phoneVerificationEnabled ? 'required' : 'nullable', 'string', 'max:10000'],
        ]);

        $nationalPhoneNumber = preg_replace('/\D+/', '', $validated['phone_number']) ?? '';
        $e164PhoneNumber = $validated['phone_prefix'].$nationalPhoneNumber;

        if (! preg_match('/^\+[1-9]\d{6,14}$/', $e164PhoneNumber)) {
            return back()
                ->withErrors(['phone_number' => __('messages.messages.phone_invalid')])
                ->withInput($request->except('firebase_id_token'));
        }

        if (config('session.driver') === 'cookie') {
            Log::critical('Firebase registration requires a server-side session driver.');

            return back()->withInput($request->except('firebase_id_token'))
                ->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        $request->session()->regenerate();
        $request->session()->put(self::REGISTRATION_SESSION_KEY, [
            'phone_prefix' => $validated['phone_prefix'],
            'phone_number' => $nationalPhoneNumber,
            'e164_phone_number' => $e164PhoneNumber,
            'firebase_id_token' => $validated['firebase_id_token'] ?? null,
            'started_at' => now()->timestamp,
        ]);
        $request->session()->forget('purchase_confirmation_email');

        try {
            $waitlistEntry = DB::table('waitlist_entries')
                ->where('phone_prefix', $validated['phone_prefix'])
                ->where('phone_number', $nationalPhoneNumber)
                ->first();

            if (! $waitlistEntry && $this->waitlistIsFull()) {
                return back()->withInput($request->except(['website', 'firebase_id_token']))
                    ->with('waitlist_error', __('messages.messages.waitlist_closed'));
            }

            if ($waitlistEntry) {
                DB::table('waitlist_entries')->where('id', $waitlistEntry->id)->update([
                    'offer_shown' => true,
                    'updated_at' => now(),
                ]);
                $waitlistEntry = DB::table('waitlist_entries')->where('id', $waitlistEntry->id)->first();
            }
        } catch (QueryException $exception) {
            Log::error('Waitlist phone signup failed.', [
                'phone_hash' => PrivacySafeLogContext::fingerprint($e164PhoneNumber),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return back()->withInput($request->except(['website', 'firebase_id_token']))
                ->with('waitlist_error', __('messages.messages.waitlist_generic_error'));
        }

        $email = $waitlistEntry?->email;
        $purchase = $email ? DB::table('purchases')
            ->where('email', $email)
            ->where('status', 'succeeded')
            ->latest('created_at')
            ->first() : null;
        $profileRequired = ! $purchase && (
            ! $this->profileComplete($waitlistEntry ?? null)
            || ! $audit->hasWaitlistLegalAcceptance($waitlistEntry?->id)
        );
        $waitlistStatus = $profileRequired ? 'registered' : 'already_registered';

        if ($email && ! $purchase && ! $profileRequired) {
            $this->sendWelcomeIfNeeded($waitlistEntry, $email, [
                'first_name' => $waitlistEntry->first_name,
            ], $emailSender);
        }

        if ($email) {
            $this->syncPurchaseConfirmationEmail($request, $email, $purchase);
        }

        return back()
            ->with('waitlist_profile_prompt', $profileRequired)
            ->with('waitlist_offer', ! $profileRequired)
            ->with('waitlist_status', $waitlistStatus)
            ->with('waitlist_email', $email ?? '')
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
        WayoutAppRegistrationService $registrationService,
        ProfileNicknameGenerator $nicknameGenerator,
    ) {
        $phoneVerificationEnabled = (bool) config('services.firebase.phone_verification_enabled', false);
        $phoneRegistration = $request->session()->get(self::REGISTRATION_SESSION_KEY);

        if (! is_array($phoneRegistration)
            || empty($phoneRegistration['e164_phone_number'])
            || (int) ($phoneRegistration['started_at'] ?? 0) < now()->subHour()->timestamp) {
            return back()->with('waitlist_error', __('messages.messages.phone_verification_expired'));
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'gender' => ['required', 'in:MALE,FEMALE,OTHER'],
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
                    'gender' => $request->input('gender', ''),
                    'phone_prefix' => $phoneRegistration['phone_prefix'],
                    'phone_number' => $phoneRegistration['phone_number'],
                    'marketing_consent' => $request->boolean('marketing_consent'),
                ]);
        }

        $validated = $validator->validated();
        $email = Str::lower($validated['email']);
        $nickname = (string) ($phoneRegistration['nickname'] ?? $nicknameGenerator->generate());

        $phoneOwner = DB::table('waitlist_entries')
            ->where('phone_prefix', $phoneRegistration['phone_prefix'])
            ->where('phone_number', $phoneRegistration['phone_number'])
            ->first();
        $emailOwner = DB::table('waitlist_entries')->where('email', $email)->first();

        if (($emailOwner && $emailOwner->id !== $phoneOwner?->id)
            || (! $phoneOwner && $this->waitlistIsFull())) {
            return back()
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_error', $emailOwner
                    ? __('messages.messages.profile_already_exists')
                    : __('messages.messages.waitlist_closed'))
                ->with('waitlist_status', $status)
                ->with('waitlist_email', $email)
                ->with('waitlist_profile', [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'],
                    'phone_prefix' => $phoneRegistration['phone_prefix'],
                    'phone_number' => $phoneRegistration['phone_number'],
                    'marketing_consent' => $request->boolean('marketing_consent'),
                ]);
        }

        $profile = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'nickname' => $nickname,
            'phone_prefix' => $phoneRegistration['phone_prefix'],
            'phone_number' => $phoneRegistration['phone_number'],
            'marketing_consent' => $request->boolean('marketing_consent'),
        ];

        if ($phoneVerificationEnabled && empty($phoneRegistration['remote_profile_created'])) {
            try {
                $verifiedFirebase = $registrationService->verifyFirebaseToken($phoneRegistration['firebase_id_token']);

                if (! hash_equals($verifiedFirebase->phoneNumber, $phoneRegistration['e164_phone_number'])) {
                    throw new WayoutRegistrationException(
                        'The Firebase token phone does not match the submitted phone.',
                        401,
                        'FIREBASE_PHONE_MISMATCH',
                    );
                }

                $registrationService->createProfile($verifiedFirebase->tempToken, [
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'email' => $email,
                    'mobile_number' => $phoneRegistration['e164_phone_number'],
                    'nickname' => $nickname,
                    'date_of_birth' => $profile['birth_date'],
                    'gender' => $profile['gender'],
                    'profile_image' => '',
                ]);
                $phoneRegistration['remote_profile_created'] = true;
                $phoneRegistration['nickname'] = $nickname;
                $request->session()->put(self::REGISTRATION_SESSION_KEY, $phoneRegistration);
            } catch (WayoutRegistrationException $exception) {
                Log::warning('WAYOUT app profile registration failed.', [
                    'email_hash' => PrivacySafeLogContext::fingerprint($email),
                    'phone_hash' => PrivacySafeLogContext::fingerprint($phoneRegistration['e164_phone_number']),
                    'ip_hash' => PrivacySafeLogContext::fingerprint($request->ip()),
                    'remote_status' => $exception->status,
                    'remote_code' => $exception->codeName,
                    ...PrivacySafeLogContext::exception($exception),
                ]);

                return back()
                    ->withInput()
                    ->with('waitlist_profile_prompt', true)
                    ->with('waitlist_error', $exception->status === 409
                        ? __('messages.messages.profile_already_exists')
                        : __('messages.messages.profile_creation_failed'))
                    ->with('waitlist_status', $status)
                    ->with('waitlist_email', $email)
                    ->with('waitlist_profile', $profile);
            }
        }

        try {
            $waitlistEntry = DB::transaction(function () use ($request, $email, $profile, $phoneRegistration, $audit) {
                $entry = DB::table('waitlist_entries')
                    ->where('phone_prefix', $phoneRegistration['phone_prefix'])
                    ->where('phone_number', $phoneRegistration['phone_number'])
                    ->lockForUpdate()
                    ->first();

                $emailOwner = DB::table('waitlist_entries')->where('email', $email)->first();
                if ($emailOwner && $emailOwner->id !== $entry?->id) {
                    throw new WayoutRegistrationException('Email already registered.', 409, 'EMAIL_ALREADY_EXISTS');
                }

                if (! $entry && $this->waitlistIsFull(lock: true)) {
                    throw new WayoutRegistrationException('Waitlist full.', 409, 'WAITLIST_FULL');
                }

                $entryData = [
                    'email' => $email,
                    ...$profile,
                    'phone_verified_at' => config('services.firebase.phone_verification_enabled') ? now() : null,
                    'offer_shown' => true,
                    'updated_at' => now(),
                ];

                if ($entry) {
                    DB::table('waitlist_entries')->where('id', $entry->id)->update($entryData);
                    $entryId = $entry->id;
                } else {
                    $entryId = (string) Str::uuid();
                    DB::table('waitlist_entries')->insert([
                        'id' => $entryId,
                        ...$entryData,
                        'created_at' => now(),
                    ]);
                }

                $audit->record(
                    $request,
                    $email,
                    'waitlist_legal',
                    'granted',
                    'waitlist_profile',
                    ['privacy', 'terms', 'waitlist_acceptance'],
                    ['waitlist_entry_id' => $entryId],
                );

                $latestMarketing = $audit->latestMarketingEvent($entryId);
                $marketingGranted = $profile['marketing_consent'];

                if ($marketingGranted && $latestMarketing?->action !== 'granted') {
                    $audit->record(
                        $request,
                        $email,
                        'marketing',
                        'granted',
                        'waitlist_profile',
                        ['marketing', 'privacy'],
                        ['waitlist_entry_id' => $entryId],
                    );
                } elseif (! $marketingGranted && (($entry?->marketing_consent ?? false) || $latestMarketing?->action === 'granted')) {
                    $audit->record(
                        $request,
                        $email,
                        'marketing',
                        'revoked',
                        'waitlist_profile',
                        ['marketing', 'privacy'],
                        ['waitlist_entry_id' => $entryId],
                        revokesEventId: $latestMarketing?->action === 'granted' ? $latestMarketing->id : null,
                    );
                }

                return DB::table('waitlist_entries')->where('id', $entryId)->first();
            });

            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
                ->first();

            $waitlistEntry = DB::table('waitlist_entries')
                ->where('email', $email)
                ->first();
        } catch (WayoutRegistrationException $exception) {
            return back()
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_error', $exception->codeName === 'WAITLIST_FULL'
                    ? __('messages.messages.waitlist_closed')
                    : __('messages.messages.profile_already_exists'))
                ->with('waitlist_status', $status)
                ->with('waitlist_email', $email)
                ->with('waitlist_profile', $profile);
        } catch (QueryException $exception) {
            Log::error('Waitlist profile completion failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                ...PrivacySafeLogContext::exception($exception),
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
        $request->session()->forget(self::REGISTRATION_SESSION_KEY);

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

    private function waitlistIsFull(bool $lock = false): bool
    {
        $query = DB::table('founder_settings')->where('key', 'waitlist_capacity');
        $capacity = (int) (($lock ? $query->lockForUpdate() : $query)->value('value')
            ?? config('founder.default_capacities.waitlist_capacity'));

        return $capacity <= 0 || DB::table('waitlist_entries')->count() >= $capacity;
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
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                ...PrivacySafeLogContext::exception($exception),
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
            'gender' => $entry->gender ?? '',
            'nickname' => $entry->nickname ?? '',
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
            && filled($entry?->gender)
            && filled($entry?->nickname)
            && filled($entry?->phone_prefix)
            && filled($entry?->phone_number)
            && (
                ! config('services.firebase.phone_verification_enabled', false)
                || filled($entry?->phone_verified_at)
            );
    }
}

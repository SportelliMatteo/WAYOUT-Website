<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WaitlistController extends Controller
{
    public function store(Request $request)
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

        $profileRequired = ! $purchase && ! $this->profileComplete($waitlistEntry ?? null);
        $waitlistStatus = $profileRequired ? 'registered' : ($alreadyRegistered ? 'already_registered' : 'registered');

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

    public function completeProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'phone_prefix' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^[0-9\s().-]{5,32}$/'],
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
        $profile = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
            'phone_prefix' => $validated['phone_prefix'],
            'phone_number' => $validated['phone_number'],
            'marketing_consent' => $request->boolean('marketing_consent'),
        ];

        try {
            DB::table('waitlist_entries')
                ->where('email', $email)
                ->update([
                    ...$profile,
                    'updated_at' => now(),
                ]);

            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
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

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array($exception->getCode(), ['23000', '23505'], true);
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
            && filled($entry?->phone_number);
    }
}

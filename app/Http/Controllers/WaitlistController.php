<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                ->with('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');
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
                        ->with('waitlist_error', 'La waitlist è chiusa: tutti i posti disponibili sono già stati riservati.');
                }
            }

            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
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
            } else {
                Log::error('Waitlist signup failed.', [
                    'email' => $email,
                    'exception' => $exception,
                ]);

                return back()->withInput($request->except('website'))
                    ->with('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');
            }
        }

        return back()
            ->with('waitlist_offer', true)
            ->with('waitlist_status', $alreadyRegistered ? 'already_registered' : 'registered')
            ->with('waitlist_email', $email)
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
}

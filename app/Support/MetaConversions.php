<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MetaConversions
{
    public static function eventId(string $name, string $reference): string
    {
        return strtolower($name).'_'.hash('sha256', $reference);
    }

    public function enabled(): bool
    {
        return config('analytics.enabled') && config('analytics.meta_capi.enabled')
            && preg_match('/^\d{5,30}$/', (string) config('analytics.meta_pixel_id'))
            && filled(config('analytics.meta_capi.access_token'))
            && preg_match('/^v\d+\.\d+$/', (string) config('analytics.meta_capi.api_version'));
    }

    public function capture(Request $request, string $email): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        $consent = json_decode((string) $request->cookie('wayout_cookie_consent'), true);
        if (! is_array($consent) || ($consent['marketing'] ?? null) !== true
            || ($consent['version'] ?? null) !== (int) config('analytics.consent_version')
            || ! Str::isUuid($consent['consentId'] ?? '')) {
            return null;
        }

        $context = [
            'consent_id' => $consent['consentId'],
            'consent_version' => $consent['version'],
            'user_data' => array_filter([
                'em' => [hash('sha256', mb_strtolower(trim($email)))],
                'client_ip_address' => $request->ip(),
                'client_user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ]),
        ];
        if (! $this->consentAllows($context)) {
            return null;
        }

        foreach (['_fbp' => 'fbp', '_fbc' => 'fbc'] as $cookie => $field) {
            $value = $request->cookie($cookie);
            if (is_string($value) && strlen($value) <= 500 && preg_match('/^fb\.\d+\.\d+\.[A-Za-z0-9_.-]+$/', $value)) {
                $context['user_data'][$field] = $value;
            }
        }

        return Crypt::encryptString(json_encode($context, JSON_THROW_ON_ERROR));
    }

    public function lead(Request $request, object $entry): void
    {
        $this->safely(function () use ($request, $entry): void {
            $context = $this->capture($request, $entry->email);
            if ($context) {
                $this->enqueue($context, 'Lead', (string) $entry->id, Carbon::parse($entry->email_verified_at)->timestamp, route('home'));
            }
        });
    }

    public function rememberCheckout(Request $request, object $purchase): void
    {
        $this->safely(function () use ($request, $purchase): void {
            DB::table('purchases')->where('id', $purchase->id)->update([
                'meta_conversion_context' => $this->capture($request, $purchase->email),
            ]);
        });
    }

    public function purchase(object $purchase): void
    {
        $this->safely(function () use ($purchase): void {
            if ($purchase->status !== 'succeeded' || empty($purchase->meta_conversion_context) || ! $this->enabled()) {
                return;
            }
            $this->enqueue(
                $purchase->meta_conversion_context,
                'Purchase',
                (string) ($purchase->order_reference ?: $purchase->id),
                Carbon::parse($purchase->updated_at)->timestamp,
                route('subscribe'),
                ['value' => ((int) $purchase->amount) / 100, 'currency' => strtoupper($purchase->currency ?: 'eur')],
            );
            DB::table('purchases')->where('id', $purchase->id)->update(['meta_conversion_context' => null]);
        });
    }

    private function enqueue(string $encryptedContext, string $name, string $reference, int $time, string $url, array $customData = []): void
    {
        $context = json_decode(Crypt::decryptString($encryptedContext), true, flags: JSON_THROW_ON_ERROR);
        if (! $this->consentAllows($context) || $time < now()->subDays(7)->timestamp) {
            return;
        }
        $eventId = self::eventId($name, $reference);
        $payload = [
            'event_name' => $name,
            'event_id' => $eventId,
            'event_time' => $time,
            'action_source' => 'website',
            'event_source_url' => $url,
            'user_data' => $context['user_data'],
        ];
        if ($customData !== []) {
            $payload['custom_data'] = $customData;
        }
        DB::table('meta_conversion_events')->insertOrIgnore([
            'event_id' => $eventId,
            'consent_id' => $context['consent_id'],
            'consent_version' => $context['consent_version'],
            'payload' => Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
            'available_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function consentAllows(array $context): bool
    {
        if ((int) $context['consent_version'] !== (int) config('analytics.consent_version')) {
            return false;
        }
        $latest = DB::table('cookie_consent_events')->where('consent_id', $context['consent_id'])
            ->orderByDesc('occurred_at')->orderByDesc('id')->first();

        return $latest && (bool) $latest->marketing
            && (int) $latest->consent_version === (int) $context['consent_version']
            && Carbon::parse($latest->occurred_at)->gt(now()->subDays((int) config('analytics.consent_days')));
    }

    /** Returns the number of failed deliveries. No tokens, payloads or response bodies enter logs. */
    public function sendPending(): int
    {
        DB::table('meta_conversion_events')->where('created_at', '<', now()->subDays(7))->delete();
        DB::table('purchases')->whereNotNull('meta_conversion_context')->where('created_at', '<', now()->subDays(7))
            ->update(['meta_conversion_context' => null]);
        if (! $this->enabled()) {
            return 0;
        }
        foreach (DB::table('purchases')->where('status', 'succeeded')->whereNotNull('meta_conversion_context')->limit(100)->get() as $purchase) {
            $this->purchase($purchase);
        }
        $failed = 0;
        $events = DB::table('meta_conversion_events')->whereNotNull('payload')->where('available_at', '<=', now())
            ->orderBy('created_at')->limit(100)->pluck('event_id');
        foreach ($events as $id) {
            $lock = Cache::lock('meta-conversion:'.$id, 30);
            if (! $lock->get()) {
                continue;
            }
            try {
                $event = DB::table('meta_conversion_events')->where('event_id', $id)->first();
                if (! $event?->payload || Carbon::parse($event->available_at)->isFuture()) {
                    continue;
                }
                if (! $this->consentAllows((array) $event)) {
                    DB::table('meta_conversion_events')->where('event_id', $id)->update(['payload' => null]);

                    continue;
                }
                $payload = json_decode(Crypt::decryptString($event->payload), true, flags: JSON_THROW_ON_ERROR);
                if ($payload['event_time'] < now()->subDays(7)->timestamp) {
                    DB::table('meta_conversion_events')->where('event_id', $id)->update(['payload' => null]);

                    continue;
                }
                $body = ['data' => [$payload]];
                if (filled(config('analytics.meta_capi.test_event_code'))) {
                    $body['test_event_code'] = config('analytics.meta_capi.test_event_code');
                }
                $response = Http::withToken(config('analytics.meta_capi.access_token'))
                    ->acceptJson()->connectTimeout(3)->timeout(10)
                    ->post('https://graph.facebook.com/'.config('analytics.meta_capi.api_version').'/'.config('analytics.meta_pixel_id').'/events', $body);
                if ($response->successful() && $response->json('events_received') === 1) {
                    DB::table('meta_conversion_events')->where('event_id', $id)->update([
                        'payload' => null, 'delivered_at' => now(), 'attempts' => $event->attempts + 1, 'last_error_code' => null,
                    ]);
                } else {
                    $this->retry($id, $event->attempts, (int) $response->json('error.code', $response->status()));
                    $failed++;
                }
            } catch (Throwable) {
                $this->retry($id, (int) ($event->attempts ?? 0), 0);
                $failed++;
            } finally {
                $lock->release();
            }
        }

        return $failed;
    }

    private function retry(string $id, int $attempts, int $code): void
    {
        DB::table('meta_conversion_events')->where('event_id', $id)->update([
            'attempts' => $attempts + 1,
            'last_error_code' => max(0, $code),
            'available_at' => now()->addMinutes(min(360, 2 ** min($attempts, 9))),
        ]);
        Log::warning('Meta conversion delivery deferred.', ['event_id' => $id, 'error_code' => $code]);
    }

    private function safely(callable $operation): void
    {
        try {
            $operation();
        } catch (Throwable) {
            Log::warning('Meta conversion could not be recorded.');
        }
    }
}

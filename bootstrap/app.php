<?php

use App\Exceptions\WayoutApiException;
use App\Http\Middleware\EnforceSiteVisibility;
use App\Http\Middleware\RequireAdminAuthentication;
use App\Http\Middleware\RequirePlansEnabled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyBenefitApiSignature;
use App\Http\Middleware\VerifyRecaptcha;
use App\Support\DataRetentionService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('meta:send-conversions')->everyMinute()->withoutOverlapping();

        $schedule->command('qonto:sync-invoices')
            ->everyTenMinutes()
            ->withoutOverlapping();

        $schedule->command('privacy:enforce-retention --trigger=scheduled')
            ->hourlyAt(DataRetentionService::SCHEDULE_MINUTE)
            ->timezone(config('app.display_timezone'))
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['wayout_cookie_consent', '_fbp', '_fbc']);

        $middleware->trustHosts(
            at: fn (): array => config('security.trusted_hosts', []),
            subdomains: false,
        );

        $middleware->alias([
            'admin.auth' => RequireAdminAuthentication::class,
            'plans.enabled' => RequirePlansEnabled::class,
            'benefit.api' => VerifyBenefitApiSignature::class,
            'recaptcha' => VerifyRecaptcha::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
            EnforceSiteVisibility::class,
        ]);

        $middleware->api(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (WayoutApiException $exception, Request $request) {
            if (! str_starts_with($exception->apiCode ?? '', 'LEGAL_') && ! str_starts_with($exception->requestPath ?? '', '/api/v1/legal/')) {
                return null;
            }
            Log::warning('Legal backend request failed.', $exception->logContext());

            return $request->expectsJson()
                ? response()->json(['error' => __('experience.legal_unavailable')], 503)
                : response()->view('errors.legal-unavailable', [], 503);
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

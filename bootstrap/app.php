<?php

use App\Http\Middleware\EnforceSiteVisibility;
use App\Http\Middleware\RequireAdminAuthentication;
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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

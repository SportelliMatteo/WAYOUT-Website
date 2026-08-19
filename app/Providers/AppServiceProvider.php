<?php

namespace App\Providers;

use App\Support\FounderAvailability;
use App\Support\LegalDocumentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'gen_random_uuid',
                static fn (): string => (string) Str::uuid(),
            );
        }

        RateLimiter::for('waitlist', function (Request $request) {
            $phonePrefix = (string) $request->input('phone_prefix');
            $phoneNumber = preg_replace('/\D+/', '', (string) $request->input('phone_number')) ?? '';

            if ($request->routeIs('waitlist.store')
                && preg_match('/^\+[1-9]\d{6,14}$/', $phonePrefix.$phoneNumber)
                && Schema::hasTable('waitlist_entries')
                && DB::table('waitlist_entries')
                    ->where('phone_prefix', $phonePrefix)
                    ->where('phone_number', $phoneNumber)
                    ->exists()) {
                return Limit::none();
            }

            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(6)->by($request->session()->getId() ?: $request->ip());
        });

        RateLimiter::for('purchase-confirmation', function (Request $request) {
            return Limit::perMinute(2)->by($request->session()->getId() ?: $request->ip());
        });

        RateLimiter::for('withdrawal', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->getId() ?: $request->ip());
        });

        RateLimiter::for('withdrawal-confirm', function (Request $request) {
            return Limit::perMinute(3)->by($request->session()->getId() ?: $request->ip());
        });

        RateLimiter::for('admin', function (Request $request) {
            if ($request->isMethod('GET')) {
                return Limit::none();
            }

            $route = $request->route()?->getName() ?: 'admin';
            $identity = Str::lower((string) ($request->input('email')
                ?: $request->session()->get('admin_pending_id')
                ?: $request->session()->get('admin_user_id')
                ?: $request->session()->getId()));

            return [
                Limit::perMinute(10)->by($route.'|'.$request->ip().'|'.$identity),
                Limit::perHour(60)->by($route.'|'.$request->ip()),
            ];
        });

        View::composer(['pages.home', 'pages.subscribe'], function ($view) {
            $availability = app(FounderAvailability::class)->summary();

            $view->with('founderCapacities', $availability['capacities'])
                ->with('founderAvailability', $availability);
        });

        View::composer(['pages.home', 'pages.subscribe', 'pages.contact'], function ($view) {
            if (! Schema::hasTable('legal_documents')) {
                return;
            }

            $documents = app(LegalDocumentService::class)->allCurrent();

            $view->with('legalDocumentsHtml', $documents->map->content_snapshot->all());
        });
    }
}

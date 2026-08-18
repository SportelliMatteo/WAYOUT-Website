<?php

namespace App\Providers;

use App\Contracts\PhoneVerificationService;
use App\Support\FirebasePhoneVerificationService;
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
        $this->app->bind(PhoneVerificationService::class, FirebasePhoneVerificationService::class);
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
            $email = Str::lower((string) $request->input('email'));

            if ($request->routeIs('waitlist.store')
                && filter_var($email, FILTER_VALIDATE_EMAIL)
                && Schema::hasTable('waitlist_entries')
                && DB::table('waitlist_entries')->where('email', $email)->exists()) {
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
            return Limit::perMinute(5)->by($request->ip());
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

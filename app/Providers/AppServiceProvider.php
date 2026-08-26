<?php

namespace App\Providers;

use App\Exceptions\WayoutApiException;
use App\Support\FounderAvailability;
use App\Support\FounderPromoCatalog;
use App\Support\LegalDocumentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        RateLimiter::for('waitlist', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(8)->by($request->ip().'|'.$email),
            ];
        });

        RateLimiter::for('benefit-api', fn (Request $request) => [
            Limit::perMinute(120)->by((string) $request->header('X-Wayout-Key', $request->ip())),
            Limit::perMinute(12)->by('benefit-email|'.Str::lower((string) $request->input('email', ''))),
        ]);

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

        View::composer('pages.home', function ($view) {
            if (! session('waitlist_offer')) {
                return;
            }

            try {
                $view->with('founderPackages', app(FounderPromoCatalog::class)->founderPackages())
                    ->with('founderCatalogError', null);
            } catch (WayoutApiException $exception) {
                Log::warning('Founder promo catalog unavailable in waitlist offer.', [
                    'status' => $exception->status,
                    'code' => $exception->apiCode,
                ]);

                $view->with('founderPackages', ['join' => null, 'creator' => null])
                    ->with('founderCatalogError', __('messages.home.catalog_unavailable'));
            }
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

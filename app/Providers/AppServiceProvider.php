<?php

namespace App\Providers;

use App\Exceptions\WayoutApiException;
use App\Support\AppStoreLinks;
use App\Support\CheckoutFeatures;
use App\Support\LegalDocumentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->scoped(LegalDocumentService::class);
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

        RateLimiter::for('benefit-api', fn (Request $request) => Limit::perMinute(120)
            ->by((string) $request->header('X-Wayout-Key', $request->ip())));

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
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('withdrawal-confirm', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('purchase-support', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

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

        View::composer('partials.app-downloads', function ($view) {
            $view->with('appStoreLinks', app(AppStoreLinks::class)->all());
        });
        View::composer('partials.commerce-support-form', function ($view) {
            $source = $view->getData()['source'];
            $key = 'commerce_support.'.$source;
            if (! request()->session()->has($key) || request()->session()->has('commerce_ticket.'.$source)) {
                request()->session()->put($key, (string) Str::uuid());
            }
            $view->with('requestId', request()->session()->get($key));
        });
        View::composer(['layouts.app', 'partials.footer', 'pages.home', 'pages.about', 'pages.faq'], function ($view) {
            $view->with('plansEnabled', app(CheckoutFeatures::class)->plansEnabled());
        });
        View::composer(['layouts.app', 'pages.legal.layout'], function ($view) {
            try {
                $documents = app(LegalDocumentService::class)->published(null);
                $view->with('footerLegalDocuments', collect($documents)->groupBy('scope')->all());
            } catch (WayoutApiException $exception) {
                Log::warning('Footer legal index unavailable.', $exception->logContext());
                $view->with('footerLegalDocuments', []);
            }
        });

        View::composer(['pages.subscribe', 'pages.contact'], function ($view) {
            if ($view->name() === 'pages.subscribe') {
                try {
                    $documents = app(LegalDocumentService::class)->published('SITE');
                    $view->with('checkoutLegalDocuments', $documents);
                    $view->with('legalContentUnavailable', $documents === []);
                } catch (WayoutApiException $exception) {
                    Log::warning('Checkout legal index unavailable.', $exception->logContext());
                    $view->with('checkoutLegalDocuments', []);
                    $view->with('legalContentUnavailable', true);
                }

                return;
            }
            if (! request()->session()->has('contact_request_id') || request()->session()->has('contact_success')) {
                request()->session()->put('contact_request_id', (string) Str::uuid());
            }
            $view->with('contactRequestId', request()->session()->get('contact_request_id'));
            try {
                $documents = app(LegalDocumentService::class);
                $documents->current('privacy');
                $documents->current('terms');
                $view->with('legalContentUnavailable', false);
            } catch (WayoutApiException $exception) {
                Log::warning('Backend legal content unavailable.', $exception->logContext());
                $view->with('legalContentUnavailable', true);
            }
        });
    }
}

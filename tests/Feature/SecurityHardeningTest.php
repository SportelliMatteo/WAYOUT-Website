<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAuthController;
use App\Support\QontoInvoiceService;
use App\Support\WayoutApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_headers_and_nonce_based_csp(): void
    {
        config()->set('security.csp_enabled', true);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-[A-Za-z0-9+\/=]+'/", $policy);
        $this->assertMatchesRegularExpression('/<script[^>]+nonce="[A-Za-z0-9+\/=]+"/', $response->getContent());
    }

    public function test_sensitive_pages_are_not_cached_or_indexed(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_plaintext_admin_bootstrap_password_is_rejected_in_production(): void
    {
        $this->app['env'] = 'production';
        config()->set('admin.email', 'admin@example.test');
        config()->set('admin.password', 'PlaintextPassword123');
        $method = new ReflectionMethod(AdminAuthController::class, 'bootstrapAdmin');

        $admin = $method->invoke(new AdminAuthController, 'admin@example.test', 'PlaintextPassword123');

        $this->assertNull($admin);
        $this->assertSame(0, DB::table('admin_users')->count());
    }

    public function test_qonto_attachment_downloads_are_limited_to_configured_https_hosts(): void
    {
        config()->set('services.qonto.attachment_hosts', 'qonto.com,amazonaws.com');
        $method = new ReflectionMethod(QontoInvoiceService::class, 'isAllowedAttachmentUrl');
        $service = new QontoInvoiceService;

        $this->assertTrue($method->invoke($service, 'https://qonto-uploads.s3.eu-central-1.amazonaws.com/invoice.pdf'));
        $this->assertTrue($method->invoke($service, 'https://files.qonto.com/invoice.pdf'));
        $this->assertFalse($method->invoke($service, 'http://files.qonto.com/invoice.pdf'));
        $this->assertFalse($method->invoke($service, 'https://127.0.0.1/internal'));
        $this->assertFalse($method->invoke($service, 'https://qonto.com@127.0.0.1/internal'));
    }

    public function test_wayout_internal_requests_are_signed_over_timestamp_and_exact_body(): void
    {
        Http::fake([
            'https://staging-app.wayoutapp.test/api/v1/internal/test' => Http::response(['data' => ['ok' => true]]),
        ]);

        app(WayoutApiClient::class)->internalPost('/api/v1/internal/test', ['promo_package_code' => 'FOUNDER_JOIN_12M_PASS']);

        Http::assertSent(function ($request): bool {
            $timestamp = $request->header('X-Wayout-Timestamp')[0] ?? '';
            $signature = $request->header('X-Wayout-Signature')[0] ?? '';

            return $signature !== ''
                && hash_equals(
                    hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48)),
                    $signature,
                );
        });
    }
}

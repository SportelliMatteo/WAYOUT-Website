<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_no_longer_exposes_a_refund_action(): void
    {
        Http::fake();
        $this->post('/admin/withdrawals/86fa730e-8c3f-4ded-99a6-aaa06ec03ab4/refund')->assertNotFound();
        Http::assertNothingSent();
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;

class CheckoutPhoneScriptTest extends TestCase
{
    public function test_checkout_phone_verification_recreates_recaptcha_safely(): void
    {
        $script = file_get_contents(resource_path('js/checkout-phone.js'));

        $this->assertStringContainsString("form.dataset.phoneVerificationInitialized === 'true'", $script);
        $this->assertStringContainsString('current.replaceWith(replacement)', $script);
        $this->assertStringContainsString('new RecaptchaVerifier(auth, recaptchaContainer', $script);
    }
}

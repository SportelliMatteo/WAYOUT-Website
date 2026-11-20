<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CustomerSession
{
    public function token(Request $request): ?string
    {
        if ((int) $request->session()->get('customer.expires_at', 0) <= now()->timestamp) {
            $request->session()->forget('customer');

            return null;
        }

        try {
            return Crypt::decryptString($request->session()->get('customer.token', ''));
        } catch (\Throwable) {
            $request->session()->forget('customer');

            return null;
        }
    }

    public function start(Request $request, string $token, array $profile): void
    {
        $request->session()->regenerate();
        $request->session()->forget(['customer_registration', 'checkout_profile', 'purchase_confirmation_email', 'checkout_purchase_id', 'checkout_plan', 'purchase_legal']);
        $request->session()->put('customer', [
            'token' => Crypt::encryptString($token),
            'expires_at' => now()->addHours(2)->timestamp,
            'profile' => $profile,
        ]);
    }
}

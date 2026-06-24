<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SubscribeController extends Controller
{
    public function checkout(Request $request)
    {
        $secret = config('services.stripe.secret');

        if (!$secret) {
            return response()->json([
                'error' => 'Stripe non è configurato. Inserisci STRIPE_SECRET in .env.',
            ], 500);
        }

        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[]' => 'card',
                'mode' => 'subscription',
                'success_url' => route('home', [], true) . '?checkout=success',
                'cancel_url' => route('subscribe', [], true),
                'line_items[0][price_data][currency]' => 'eur',
                'line_items[0][price_data][product_data][name]' => 'WAYOUT Early Access - 50% off',
                'line_items[0][price_data][product_data][description]' => '50% off for first 12 months + 60 days trial',
                'line_items[0][price_data][unit_amount]' => '299',
                'line_items[0][price_data][recurring][interval]' => 'month',
                'line_items[0][quantity]' => '1',
            ]);

        if ($response->failed()) {
            return response()->json([
                'error' => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'sessionId' => $response->json('id'),
        ]);
    }
}

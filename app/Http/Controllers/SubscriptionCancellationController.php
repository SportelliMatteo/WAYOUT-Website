<?php

namespace App\Http\Controllers;

class SubscriptionCancellationController extends Controller
{
    public function create()
    {
        return response()->view('pages.subscription-cancellation')->header('Cache-Control', 'no-store, private');
    }
}

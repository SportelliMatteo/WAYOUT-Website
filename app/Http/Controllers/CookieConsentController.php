<?php

namespace App\Http\Controllers;

use App\Support\DatabaseUuid;
use App\Support\PrivacySafeLogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'consent_id' => ['required', 'uuid'],
            'consent_version' => ['required', 'integer', 'min:1', 'max:10000'],
            'analytics' => ['required', 'boolean'],
            'marketing' => ['required', 'boolean'],
        ]);

        DB::table('cookie_consent_events')->insert([
            'id' => DatabaseUuid::new(),
            'consent_id' => $data['consent_id'],
            'consent_version' => $data['consent_version'],
            'analytics' => $data['analytics'],
            'marketing' => $data['marketing'],
            'ip_hash' => PrivacySafeLogContext::fingerprint($request->ip()),
            'user_agent_hash' => PrivacySafeLogContext::fingerprint($request->userAgent()),
            'occurred_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        return response()->noContent();
    }
}

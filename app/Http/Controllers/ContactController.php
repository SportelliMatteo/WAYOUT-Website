<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        if (filled($request->input('website'))) {
            Log::warning('Contact honeypot triggered.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'subject' => ['required', Rule::in(array_keys(__('messages.contact.subject_options')))],
                'message' => ['required', 'string', 'max:5000'],
                'privacy_accepted' => ['accepted'],
            ],
            [
                'privacy_accepted.accepted' => __('messages.contact.privacy_required'),
            ],
        );

        unset($validated['privacy_accepted']);
        $validated['subject'] = __('messages.contact.subject_options.'.$validated['subject']);

        try {
            DB::table('contact_messages')->insert([
                ...$validated,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $exception) {
            Log::error('Contact message insert failed.', [
                'email' => $validated['email'],
                'exception' => $exception,
            ]);

            return back()->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }

        return back()->with('contact_success', true);
    }
}

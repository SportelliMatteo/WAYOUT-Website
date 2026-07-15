<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Support\TransactionalEmailSender;
use App\Support\ConsentAuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request, TransactionalEmailSender $emailSender, ConsentAuditService $audit)
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
            DB::transaction(function () use ($request, $validated, $audit) {
                $contactId = DB::table('contact_messages')->insertGetId([
                    ...$validated,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $audit->record(
                    $request,
                    $validated['email'],
                    'contact_legal',
                    'granted',
                    'contact_form',
                    ['privacy', 'terms', 'contact_acceptance'],
                    ['contact_message_id' => $contactId],
                );
            });
        } catch (QueryException $exception) {
            Log::error('Contact message insert failed.', [
                'email' => $validated['email'],
                'exception' => $exception,
            ]);

            return back()->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }

        $recipient = config('email.contact_recipient');

        if (filled($recipient)) {
            try {
                $emailSender->send($recipient, new ContactMessageMail($validated));
            } catch (Throwable $exception) {
                Log::error('Contact notification email failed.', [
                    'contact_email' => $validated['email'],
                    'exception' => $exception,
                ]);
            }
        } else {
            Log::warning('Contact notification skipped because CONTACT_EMAIL and ADMIN_EMAIL are missing.');
        }

        return back()->with('contact_success', true);
    }
}

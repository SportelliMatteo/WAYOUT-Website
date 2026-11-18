<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Mail\ContactMessageMail;
use App\Support\ConsentAuditService;
use App\Support\PrivacySafeLogContext;
use App\Support\PurchaseSupportService;
use App\Support\TransactionalEmailSender;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request, TransactionalEmailSender $emailSender, ConsentAuditService $audit, PurchaseSupportService $support)
    {
        if (filled($request->input('website'))) {
            Log::warning('Contact honeypot triggered.', [
                'ip_hash' => PrivacySafeLogContext::fingerprint($request->ip()),
                'user_agent_hash' => PrivacySafeLogContext::fingerprint($request->userAgent()),
            ]);

            return back()->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }

        $validated = $request->validate(
            [
                'request_id' => ['required', 'uuid'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'subject' => ['required', Rule::in(array_keys(PurchaseSupportService::CONTACT_CATEGORIES))],
                'message' => ['required', 'string', 'max:5000'],
                'privacy_accepted' => ['accepted'],
            ],
            [
                'privacy_accepted.accepted' => __('messages.contact.privacy_required'),
            ],
        );

        $requestId = $validated['request_id'];
        if (! hash_equals((string) $request->session()->get('contact_request_id', ''), $requestId)) {
            return redirect()->route('contact')->withInput($request->except('website'))
                ->with('contact_error', 'Il modulo è scaduto. Compilalo nuovamente.');
        }
        $subject = $validated['subject'];
        unset($validated['privacy_accepted'], $validated['request_id']);
        $validated['email'] = Str::lower(trim($validated['email']));
        $validated['subject'] = __('messages.contact.subject_options.'.$validated['subject']);

        try {
            $contactId = DB::transaction(function () use ($request, $validated, $audit, $requestId) {
                if (DB::table('contact_messages')->where('id', $requestId)->exists()) {
                    return $requestId;
                }
                $contactId = $requestId;
                DB::table('contact_messages')->insert([
                    'id' => $contactId,
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
                    ['privacy', 'terms'],
                    ['contact_message_id' => $contactId],
                );

                return $contactId;
            });
        } catch (QueryException $exception) {
            Log::error('Contact message insert failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($validated['email']),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return back()->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }

        try {
            $alreadySent = filled(DB::table('purchase_support_requests')->where('id', $contactId)->value('backend_ticket_id'));
            $support->submit($request, $contactId, PurchaseSupportService::CONTACT,
                $validated['name'], $validated['email'], $validated['message'], $subject);
        } catch (WayoutApiException $exception) {
            Log::warning('Contact support ticket not confirmed.', $exception->logContext());

            return redirect()->route('contact')->withInput($request->except('website'))
                ->with('contact_error', __('messages.messages.contact_error'));
        }
        if ($alreadySent) {
            return back()->with('contact_success', true);
        }

        $recipient = config('email.contact_recipient');

        if (filled($recipient)) {
            try {
                $emailSender->send($recipient, new ContactMessageMail($validated));
            } catch (Throwable $exception) {
                Log::error('Contact notification email failed.', [
                    'contact_email_hash' => PrivacySafeLogContext::fingerprint($validated['email']),
                    ...PrivacySafeLogContext::exception($exception),
                ]);
            }
        } else {
            Log::warning('Contact notification skipped because CONTACT_EMAIL and ADMIN_EMAIL are missing.');
        }

        return back()->with('contact_success', true);
    }
}

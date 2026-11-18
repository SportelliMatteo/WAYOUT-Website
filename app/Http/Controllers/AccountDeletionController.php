<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Support\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountDeletionController extends Controller
{
    public function create(Request $request)
    {
        $previousId = $request->session()->get('account_deletion_request_id');
        $previousSent = is_string($previousId) && DB::table('account_deletion_requests')
            ->where('id', $previousId)->whereNotNull('backend_ticket_id')->exists();
        if (! $previousId || $previousSent) {
            $request->session()->put('account_deletion_request_id', (string) Str::uuid());
        }

        return response()->view('pages.account-deletion', [
            'requestId' => $request->session()->get('account_deletion_request_id'),
            'warning' => AccountDeletionService::WARNING,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, AccountDeletionService $service)
    {
        $validated = $request->validate([
            'website' => ['nullable', 'max:0'],
            'request_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^\+?[0-9 ().-]+$/D', 'regex:/^(?:\D*\d){7,15}\D*$/D'],
            'consequences_accepted' => ['required', 'accepted'],
        ], ['consequences_accepted.accepted' => 'Conferma di aver compreso le conseguenze della cancellazione.']);
        if (! hash_equals((string) $request->session()->get('account_deletion_request_id', ''), $validated['request_id'])) {
            return redirect()->route('account.deletion')->withErrors(['support' => 'Il modulo è scaduto. Compilalo nuovamente.']);
        }

        try {
            $ticket = $service->submit($request, $validated['request_id'], trim($validated['name']), Str::lower(trim($validated['email'])), trim($validated['phone']));
        } catch (WayoutApiException $exception) {
            Log::warning('Account deletion ticket not confirmed.', $exception->logContext());

            return redirect()->route('account.deletion')->withInput($request->except('website'))->withErrors([
                'support' => 'Non è stato possibile confermare l’invio. Contatta l’assistenza indicando il riferimento '.$validated['request_id'].'.',
            ]);
        }

        return redirect()->route('account.deletion')->with('account_deletion_ticket', $ticket);
    }
}

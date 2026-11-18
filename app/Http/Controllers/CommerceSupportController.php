<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Support\PurchaseSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommerceSupportController extends Controller
{
    public function withdrawal(Request $request, PurchaseSupportService $support)
    {
        return $this->submit($request, $support, PurchaseSupportService::WITHDRAWAL);
    }

    public function cancellation(Request $request, PurchaseSupportService $support)
    {
        return $this->submit($request, $support, PurchaseSupportService::NON_RENEWAL);
    }

    private function submit(Request $request, PurchaseSupportService $support, string $source)
    {
        $route = $source === PurchaseSupportService::WITHDRAWAL ? 'legal.refunds' : 'subscription.cancel';
        $validated = $request->validate([
            'website' => ['nullable', 'max:0'],
            'request_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^\+?[0-9 ().-]+$/D', 'regex:/^(?:\D*\d){7,15}\D*$/D'],
            'message' => ['required', 'string', 'max:4000'],
            'request_confirmed' => ['required', 'accepted'],
        ]);
        if (! hash_equals((string) $request->session()->get('commerce_support.'.$source, ''), $validated['request_id'])) {
            return redirect()->route($route)->withErrors(['support' => 'Il modulo è scaduto. Compilalo nuovamente.']);
        }

        try {
            $ticket = $support->submit($request, $validated['request_id'], $source, trim($validated['name']),
                Str::lower(trim($validated['email'])), trim($validated['message'])."\n\nTelefono di contatto: ".trim($validated['phone']));
        } catch (WayoutApiException $exception) {
            Log::warning('Commerce support ticket not confirmed.', $exception->logContext());

            return redirect()->route($route)->withInput($request->except('website'))->withErrors([
                'support' => 'Non è stato possibile confermare l’invio. Contatta l’assistenza indicando il riferimento '.$validated['request_id'].'.',
            ]);
        }

        return redirect()->route($route)->with('commerce_ticket.'.$source, $ticket);
    }
}

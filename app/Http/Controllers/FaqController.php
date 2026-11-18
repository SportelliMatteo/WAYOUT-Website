<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Support\SiteFaqService;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    public function __invoke(SiteFaqService $faqs)
    {
        try {
            return response()->view('pages.faq', ['sections' => $faqs->sections(), 'faqUnavailable' => false]);
        } catch (WayoutApiException $exception) {
            Log::warning('Site FAQs unavailable.', $exception->logContext());

            return response()->view('pages.faq', ['sections' => [], 'faqUnavailable' => true], 503);
        }
    }
}

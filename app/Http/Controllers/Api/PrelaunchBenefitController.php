<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\WaitlistBenefitService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PrelaunchBenefitController extends Controller
{
    public function index(Request $request, WaitlistBenefitService $benefits)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.max(1, (int) config('benefits.api_max_per_page', 200))],
        ]);
        $perPage = (int) ($validated['per_page'] ?? config('benefits.api_default_per_page', 100));
        $paginator = $benefits->eligibleQuery()
            ->orderBy('waitlist_position')
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()->map(fn (object $entry): array => $this->benefitData($entry))->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function eligibility(Request $request, WaitlistBenefitService $benefits)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $entry = $benefits->findEligibleByEmail($validated['email']);

        return response()->json([
            'success' => true,
            'data' => [
                'eligible' => $entry !== null,
                'benefit' => $entry ? $this->benefitData($entry) : null,
            ],
        ]);
    }

    /** @return array{benefit_id: string, email: string, waitlist_position: int, benefit_type: string, duration_days: int, email_verified_at: string} */
    private function benefitData(object $entry): array
    {
        return [
            'benefit_id' => $entry->benefit_id,
            'email' => $entry->email,
            'waitlist_position' => (int) $entry->waitlist_position,
            'benefit_type' => 'waitlist',
            'duration_days' => WaitlistBenefitService::DURATION_DAYS,
            'email_verified_at' => Carbon::parse($entry->email_verified_at)->toISOString(),
        ];
    }
}

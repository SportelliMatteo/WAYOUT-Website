<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        if ($this->isAuthenticated($request)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        $adminEmail = config('admin.email');
        $adminPassword = config('admin.password');

        if (! $adminEmail || ! $adminPassword) {
            throw ValidationException::withMessages([
                'email' => __('messages.messages.admin_missing_credentials'),
            ]);
        }

        $passwordMatches = Str::startsWith($adminPassword, ['$2y$', '$argon2id$', '$argon2i$'])
            ? Hash::check($validated['password'], $adminPassword)
            : hash_equals($adminPassword, $validated['password']);

        if (! hash_equals(Str::lower($adminEmail), Str::lower($validated['email'])) || ! $passwordMatches) {
            throw ValidationException::withMessages([
                'email' => __('messages.messages.admin_invalid_credentials'),
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function dashboard(Request $request)
    {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => $request->query('status', 'all'),
            'plan' => $request->query('plan', 'all'),
        ];

        $purchaseSummary = DB::table('purchases')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as succeeded_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) as succeeded_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'join' THEN 1 ELSE 0 END) as join_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'join' THEN amount ELSE 0 END) as join_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'creator' THEN 1 ELSE 0 END) as creator_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'creator' THEN amount ELSE 0 END) as creator_revenue")
            ->first();

        $stats = [
            'waitlist_total' => DB::table('waitlist_entries')->count(),
            'waitlist_offer_shown' => DB::table('waitlist_entries')->where('offer_shown', true)->count(),
            'buyers_total' => DB::table('purchases')->where('status', 'succeeded')->distinct('email')->count('email'),
            'orders_total' => (int) ($purchaseSummary->total_orders ?? 0),
            'orders_succeeded' => (int) ($purchaseSummary->succeeded_orders ?? 0),
            'revenue_total' => (int) ($purchaseSummary->succeeded_revenue ?? 0),
            'join_orders' => (int) ($purchaseSummary->join_orders ?? 0),
            'join_revenue' => (int) ($purchaseSummary->join_revenue ?? 0),
            'creator_orders' => (int) ($purchaseSummary->creator_orders ?? 0),
            'creator_revenue' => (int) ($purchaseSummary->creator_revenue ?? 0),
        ];

        $capacities = $this->founderCapacities();

        $purchaseAggregate = DB::table('purchases')
            ->select('email')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as orders_succeeded")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) as revenue_total")
            ->selectRaw('MAX(created_at) as latest_purchase_at')
            ->selectRaw($this->successfulPlansExpression())
            ->groupBy('email');

        $waitlistQuery = DB::table('waitlist_entries')
            ->leftJoinSub($purchaseAggregate, 'purchase_totals', function ($join) {
                $join->on('waitlist_entries.email', '=', 'purchase_totals.email');
            })
            ->select([
                'waitlist_entries.email',
                'waitlist_entries.offer_shown',
                'waitlist_entries.created_at',
                'waitlist_entries.updated_at',
                DB::raw('COALESCE(purchase_totals.orders_total, 0) as orders_total'),
                DB::raw('COALESCE(purchase_totals.orders_succeeded, 0) as orders_succeeded'),
                DB::raw('COALESCE(purchase_totals.revenue_total, 0) as revenue_total'),
                'purchase_totals.latest_purchase_at',
                'purchase_totals.plans',
            ])
            ->orderByDesc('waitlist_entries.created_at');

        if ($filters['q'] !== '') {
            $waitlistQuery->where('waitlist_entries.email', 'like', '%'.$filters['q'].'%');
        }

        if ($filters['status'] === 'buyers') {
            $waitlistQuery->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) > 0');
        } elseif ($filters['status'] === 'no_purchase') {
            $waitlistQuery->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) = 0');
        }

        if (in_array($filters['plan'], ['join', 'creator'], true)) {
            $waitlistQuery->whereExists(function ($query) use ($filters) {
                $query->selectRaw('1')
                    ->from('purchases')
                    ->whereColumn('purchases.email', 'waitlist_entries.email')
                    ->where('purchases.status', 'succeeded')
                    ->where('purchases.plan', $filters['plan']);
            });
        }

        $waitlistEntries = $waitlistQuery
            ->paginate(20)
            ->withQueryString();

        $recentPurchases = DB::table('purchases')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $joinBuyers = $this->buyersForPlan('join');
        $creatorBuyers = $this->buyersForPlan('creator');

        return view('admin.dashboard', [
            'filters' => $filters,
            'stats' => $stats,
            'capacities' => $capacities,
            'waitlistEntries' => $waitlistEntries,
            'recentPurchases' => $recentPurchases,
            'joinBuyers' => $joinBuyers,
            'creatorBuyers' => $creatorBuyers,
            'planLabels' => $this->planLabels(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'waitlist_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'join_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'creator_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        foreach ($validated as $key => $value) {
            DB::table('founder_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return back()->with('admin_success', __('messages.admin.admin_success'));
    }

    private function buyersForPlan(string $plan)
    {
        return DB::table('purchases')
            ->select('email')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw('SUM(amount) as revenue_total')
            ->selectRaw('MAX(created_at) as latest_purchase_at')
            ->where('status', 'succeeded')
            ->where('plan', $plan)
            ->groupBy('email')
            ->orderByDesc('latest_purchase_at')
            ->limit(20)
            ->get();
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function isAuthenticated(Request $request): bool
    {
        return $request->session()->has('admin_authenticated');
    }

    private function planLabels(): array
    {
        return [
            'join' => 'Founder Join 12M',
            'creator' => 'Founder Creator 12M',
        ];
    }

    private function successfulPlansExpression(): string
    {
        return "STRING_AGG(DISTINCT CASE WHEN status = 'succeeded' THEN plan END, ',') as plans";
    }

    private function founderCapacities(): array
    {
        $defaults = config('founder.default_capacities');

        if (! Schema::hasTable('founder_settings')) {
            return $defaults;
        }

        return array_replace(
            $defaults,
            DB::table('founder_settings')
                ->pluck('value', 'key')
                ->map(fn ($value) => (int) $value)
                ->all()
        );
    }
}

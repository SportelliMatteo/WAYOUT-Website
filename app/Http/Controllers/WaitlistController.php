<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        DB::table('waitlist_entries')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'offer_shown' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', true);
    }
}
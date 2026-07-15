<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Support\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function store(Request $request, AdminAuditService $audit)
    {
        /** @var AdminUser $creator */
        $creator = $request->attributes->get('admin_user');
        $maxUsers = max(1, (int) config('admin.max_users', 4));

        if (AdminUser::query()->count() >= $maxUsers) {
            throw ValidationException::withMessages([
                'email' => __('messages.admin.max_users_reached', ['count' => $maxUsers]),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:admin_users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $admin = AdminUser::query()->create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        Log::info('Admin account created.', [
            'admin_user_id' => $admin->id,
            'created_by_admin_user_id' => $creator->id,
        ]);

        $audit->record(
            $request,
            'admin_user.created',
            'admin_user',
            $admin->id,
            $admin->email,
            newValues: [
                'name' => $admin->name,
                'email' => $admin->email,
                'is_active' => $admin->is_active,
            ],
        );

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])
            ->with('admin_success', __('messages.admin.user_created', ['email' => $admin->email]));
    }
}

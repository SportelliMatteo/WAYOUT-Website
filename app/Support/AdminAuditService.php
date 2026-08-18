<?php

namespace App\Support;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminAuditService
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function record(
        Request $request,
        string $action,
        string $targetType,
        int|string|null $targetId = null,
        ?string $targetLabel = null,
        array $oldValues = [],
        array $newValues = [],
    ): string {
        /** @var AdminUser|null $actor */
        $actor = $request->attributes->get('admin_user');

        abort_unless($actor, 403);

        return DatabaseUuid::insert('admin_audit_events', [
            'admin_user_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_email' => $actor->email,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => filled($targetId) ? (string) $targetId : null,
            'target_label' => $targetLabel,
            'old_values' => $oldValues === [] ? null : json_encode($oldValues, JSON_THROW_ON_ERROR),
            'new_values' => $newValues === [] ? null : json_encode($newValues, JSON_THROW_ON_ERROR),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }
}

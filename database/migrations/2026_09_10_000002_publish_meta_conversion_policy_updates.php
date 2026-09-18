<?php

use App\Support\LegalDocumentService;
use App\Support\MetaTrackingPolicyUpdate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $documents = app(LegalDocumentService::class);
        foreach (['privacy', 'cookies'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $current = $documents->current($key, $locale);
                $content = MetaTrackingPolicyUpdate::apply($current->content_snapshot, $locale);
                if ($content !== $current->content_snapshot) {
                    $documents->publish($key, $locale, '2026-09-10-meta-capi', $current->title, $current->description, $content);
                }
            }
        }
    }

    public function down(): void
    {
        // Keep immutable legal history and the published disclosure on rollback.
    }
};

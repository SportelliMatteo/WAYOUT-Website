<?php

use App\Support\LegalDocumentService;
use App\Support\WaitlistCreatorPolicyUpdate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $documents = app(LegalDocumentService::class);

            foreach (['terms', 'passes'] as $key) {
                foreach (['it', 'en'] as $locale) {
                    $current = $documents->current($key, $locale);
                    $content = WaitlistCreatorPolicyUpdate::apply($current->content_snapshot, $key, $locale);

                    if ($content !== $current->content_snapshot) {
                        $documents->publish($key, $locale, '2026-09-18-waitlist-creator', $current->title, $current->description, $content);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Preserve published legal versions and existing consent audit records.
    }
};

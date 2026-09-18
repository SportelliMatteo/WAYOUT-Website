<?php

use App\Support\LegalDocumentService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $documents = app(LegalDocumentService::class);

        foreach (['sales', 'presale', 'passes'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $current = $documents->current($key, $locale);
                $content = preg_replace_callback(
                    '/\b(600|200)(?=\s+pass(?:es)?\b)/u',
                    fn (array $match): string => $match[1] === '600' ? '500' : '150',
                    $current->content_snapshot,
                );

                if ($content !== $current->content_snapshot) {
                    $documents->publish($key, $locale, '2026-09-18-founder-capacities', $current->title, $current->description, $content);
                }
            }
        }
    }

    public function down(): void
    {
        // Published legal versions remain available in the audit history.
    }
};

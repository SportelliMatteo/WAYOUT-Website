<?php

use App\Support\LegalDocumentService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $documents = app(LegalDocumentService::class);

        foreach (['sales', 'presale'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $current = $documents->current($key, $locale);

                if (str_contains($current->content_snapshot, '+393522164100')) {
                    continue;
                }

                $label = $locale === 'it' ? 'Telefono' : 'Phone';
                $contact = '<p class="mt-3"><strong>'.$label.' WAYOUT:</strong> +393522164100</p>';
                $content = $contact.$current->content_snapshot;

                $documents->publish($key, $locale, '2026-09-18-seller-phone', $current->title, $current->description, $content);
            }
        }
    }

    public function down(): void
    {
        // Published legal versions remain available in the audit history.
    }
};

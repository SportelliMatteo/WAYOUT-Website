<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $currentVersionIds = DB::table('legal_documents')
            ->whereNotNull('current_version_id')
            ->pluck('current_version_id');

        if ($currentVersionIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($currentVersionIds) {
            DB::table('legal_document_versions')
                ->whereNotIn('id', $currentVersionIds)
                ->delete();

            DB::table('legal_document_versions')
                ->whereIn('id', $currentVersionIds)
                ->update([
                    'version' => now()->toDateString(),
                    'published_at' => now(),
                    'source_path' => 'legal_baseline_reset',
                ]);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Deleted legal-document history cannot be reconstructed.');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const BASELINE_VERSION = '1.0';

    private const BASELINE_DATE = '2026-08-31';

    public function up(): void
    {
        DB::transaction(function () {
            $currentVersionIds = DB::table('legal_documents')
                ->whereNotNull('current_version_id')
                ->pluck('current_version_id');

            if ($currentVersionIds->isEmpty()) {
                return;
            }

            DB::table('legal_document_versions')
                ->whereNotIn('id', $currentVersionIds)
                ->delete();

            DB::table('legal_document_versions')
                ->whereIn('id', $currentVersionIds)
                ->update([
                    'version' => self::BASELINE_VERSION,
                    'published_at' => self::BASELINE_DATE.' 00:00:00',
                ]);

            DB::table('legal_documents')->update([
                'updated_at' => self::BASELINE_DATE.' 00:00:00',
            ]);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Deleted legal-document history cannot be reconstructed.');
    }
};

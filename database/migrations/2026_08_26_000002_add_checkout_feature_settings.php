<?php

use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('founder_settings')) {
            return;
        }

        $exists = DB::table('founder_settings')
            ->where('key', CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY)
            ->exists();

        if (! $exists) {
            DB::table('founder_settings')->insert([
                'id' => DatabaseUuid::new(),
                'key' => CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY,
                'value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('founder_settings')) {
            DB::table('founder_settings')
                ->where('key', CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY)
                ->delete();
        }
    }
};

<?php

use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve the currently active purchase flow on existing installations.
        DB::table('founder_settings')->insertOrIgnore([
            'id' => DatabaseUuid::new(), 'key' => CheckoutFeatures::PLANS_ENABLED_KEY,
            'value' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('founder_settings')->where('key', CheckoutFeatures::PLANS_ENABLED_KEY)->delete();
    }
};

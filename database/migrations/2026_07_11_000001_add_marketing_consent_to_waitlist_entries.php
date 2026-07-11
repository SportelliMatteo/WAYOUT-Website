<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('waitlist_entries') && ! Schema::hasColumn('waitlist_entries', 'marketing_consent')) {
            Schema::table('waitlist_entries', function (Blueprint $table) {
                $table->boolean('marketing_consent')->default(false)->after('phone_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('waitlist_entries') && Schema::hasColumn('waitlist_entries', 'marketing_consent')) {
            Schema::table('waitlist_entries', function (Blueprint $table) {
                $table->dropColumn('marketing_consent');
            });
        }
    }
};

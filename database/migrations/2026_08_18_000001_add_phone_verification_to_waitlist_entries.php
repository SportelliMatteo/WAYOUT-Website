<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->string('firebase_uid', 128)->nullable()->after('phone_number');
            $table->timestamp('phone_verified_at')->nullable()->after('firebase_uid');
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->dropColumn(['firebase_uid', 'phone_verified_at']);
        });
    }
};

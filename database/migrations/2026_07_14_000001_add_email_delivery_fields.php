<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->timestamp('welcome_email_sent_at')->nullable();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('confirmation_email_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_entries', fn (Blueprint $table) => $table->dropColumn('welcome_email_sent_at'));
        Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn('confirmation_email_sent_at'));
    }
};

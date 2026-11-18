<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_support_requests', function (Blueprint $table) {
            $table->uuid('purchase_id')->nullable()->change();
            $table->string('email_hash', 64)->nullable();
        });
    }

    public function down(): void
    {
        // Keep nullable purchase references: existing requests may have no order.
        Schema::table('purchase_support_requests', fn (Blueprint $table) => $table->dropColumn('email_hash'));
    }
};

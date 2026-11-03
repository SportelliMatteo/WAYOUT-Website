<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('offer_type', 32)->nullable();
            $table->uuid('offer_id')->nullable();
            $table->string('external_price_id')->nullable();
            $table->string('billing_period', 32)->nullable();
            $table->boolean('auto_renew')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['offer_type', 'offer_id', 'external_price_id', 'billing_period', 'auto_renew']);
        });
    }
};

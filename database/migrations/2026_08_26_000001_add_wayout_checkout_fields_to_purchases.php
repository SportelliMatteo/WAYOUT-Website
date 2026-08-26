<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->uuid('wayout_user_id')->nullable()->after('waitlist_entry_id')->index();
            $table->string('promo_package_code', 80)->nullable()->after('plan')->index();
            $table->string('wayout_subscription_id')->nullable()->after('stripe_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['wayout_user_id']);
            $table->dropIndex(['promo_package_code']);
            $table->dropColumn(['wayout_user_id', 'promo_package_code', 'wayout_subscription_id']);
        });
    }
};

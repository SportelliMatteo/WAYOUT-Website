<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cookie_consent_events', fn (Blueprint $table) => $table->timestamp('occurred_at', 6)->change());

        Schema::create('meta_conversion_events', function (Blueprint $table): void {
            $table->string('event_id', 80)->primary();
            $table->uuid('consent_id')->index();
            $table->unsignedInteger('consent_version');
            $table->text('payload')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('last_error_code')->nullable();
            $table->timestamp('available_at')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('created_at')->index();
        });
        Schema::table('purchases', function (Blueprint $table): void {
            $table->text('meta_conversion_context')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn('meta_conversion_context'));
        Schema::dropIfExists('meta_conversion_events');
    }
};

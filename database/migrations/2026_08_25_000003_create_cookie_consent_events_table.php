<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consent_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('consent_id')->index();
            $table->unsignedInteger('consent_version');
            $table->boolean('analytics');
            $table->boolean('marketing');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consent_events');
    }
};

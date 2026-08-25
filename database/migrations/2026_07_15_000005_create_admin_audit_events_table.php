<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('actor_name', 120);
            $table->string('actor_email');
            $table->string('action', 100)->index();
            $table->string('target_type', 100);
            $table->uuid('target_id')->nullable();
            $table->string('target_label')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_events');
    }
};

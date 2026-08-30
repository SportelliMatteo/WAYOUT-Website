<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_retention_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('trigger', 32);
            $table->boolean('dry_run')->default(false);
            $table->string('status', 16)->index();
            $table->json('results')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('waitlist_email_verifications', function (Blueprint $table) {
            $table->index('consumed_at', 'waitlist_email_verifications_consumed_at_index');
        });

        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->index(
                ['email_verified_at', 'created_at'],
                'waitlist_entries_verification_created_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->dropIndex('waitlist_entries_verification_created_index');
        });

        Schema::table('waitlist_email_verifications', function (Blueprint $table) {
            $table->dropIndex('waitlist_email_verifications_consumed_at_index');
        });

        Schema::dropIfExists('data_retention_runs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->string('document_key', 64);
            $table->string('version', 64);
            $table->string('locale', 10);
            $table->string('content_hash', 64);
            $table->longText('content_snapshot');
            $table->string('content_format', 32)->default('blade_source');
            $table->string('source_path');
            $table->timestamp('published_at');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_key', 'version', 'locale']);
        });

        Schema::create('consent_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waitlist_entry_id')->nullable()->constrained('waitlist_entries')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('contact_message_id')->nullable()->constrained('contact_messages')->nullOnDelete();
            $table->foreignId('revokes_event_id')->nullable()->constrained('consent_events')->nullOnDelete();
            $table->string('subject_email')->index();
            $table->string('consent_type', 64)->index();
            $table->string('action', 16)->index();
            $table->string('source', 64)->index();
            $table->json('document_versions');
            $table->json('document_hashes');
            $table->json('document_urls');
            $table->string('locale', 10);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id_hash', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['waitlist_entry_id', 'consent_type', 'occurred_at']);
            $table->index(['purchase_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_events');
        Schema::dropIfExists('legal_document_versions');
    }
};

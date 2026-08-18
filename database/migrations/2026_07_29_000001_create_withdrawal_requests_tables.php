<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->string('receipt_number', 32)->unique();
            $table->char('public_token_hash', 64)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('purchase_email');
            $table->string('receipt_email');
            $table->string('order_reference', 32)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('plan', 32);
            $table->text('declaration');
            $table->json('document_versions');
            $table->json('document_hashes');
            $table->timestamp('submitted_at');
            $table->timestamp('ordinary_deadline_at')->nullable();
            $table->boolean('within_ordinary_period')->nullable();
            $table->char('submitted_ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->string('status', 32)->default('received');
            $table->string('refund_status', 32)->default('pending');
            $table->timestamp('receipt_email_sent_at')->nullable();
            $table->timestamp('receipt_email_failed_at')->nullable();
            $table->timestamps();

            $table->index(['purchase_email', 'submitted_at']);
            $table->index(['status', 'submitted_at']);
        });

        Schema::create('withdrawal_request_events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->foreignUuid('withdrawal_request_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 64);
            $table->string('actor_type', 32)->default('system');
            $table->uuid('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['withdrawal_request_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_request_events');
        Schema::dropIfExists('withdrawal_requests');
    }
};

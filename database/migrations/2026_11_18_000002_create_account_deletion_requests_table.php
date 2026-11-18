<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email_hash', 64);
            $table->string('status', 20)->default('pending');
            $table->uuid('backend_ticket_id')->nullable();
            $table->timestamp('acknowledged_at');
            $table->string('warning_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
    }
};

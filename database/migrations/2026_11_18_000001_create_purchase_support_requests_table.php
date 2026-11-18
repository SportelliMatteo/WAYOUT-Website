<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_support_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained('purchases');
            $table->string('source', 60);
            $table->string('status', 20)->default('pending');
            $table->uuid('backend_ticket_id')->nullable();
            $table->timestamps();
            $table->unique(['purchase_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_support_requests');
    }
};

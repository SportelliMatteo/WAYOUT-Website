<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
                $table->string('email');
                $table->string('plan');
                $table->integer('amount');
                $table->string('currency', 3)->default('eur');
                $table->string('stripe_session_id')->nullable();
                $table->string('order_reference', 64)->nullable()->unique();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

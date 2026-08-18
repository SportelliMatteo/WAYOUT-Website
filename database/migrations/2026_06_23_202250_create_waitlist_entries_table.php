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
        if (! Schema::hasTable('waitlist_entries')) {
            Schema::create('waitlist_entries', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
                $table->string('email')->unique();
                $table->boolean('offer_shown')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};

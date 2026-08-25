<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'nickname',
                'birth_date',
                'gender',
                'phone_prefix',
                'phone_number',
                'phone_verified_at',
                'offer_shown',
            ]);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['phone_prefix', 'phone_number']);
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('nickname', 64)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 16)->nullable();
            $table->string('phone_prefix', 8)->nullable();
            $table->string('phone_number', 32)->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('offer_shown')->default(true);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('phone_prefix', 8)->nullable();
            $table->string('phone_number', 32)->nullable();
        });
    }
};

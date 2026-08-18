<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('password');
            $table->text('totp_secret')->nullable();
            $table->json('recovery_codes')->nullable();
            $table->unsignedBigInteger('last_totp_step')->nullable();
            $table->timestamp('totp_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedInteger('auth_version')->default(1);
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};

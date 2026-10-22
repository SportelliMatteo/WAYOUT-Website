<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('package_name')->nullable();
            $table->timestamp('package_start_at')->nullable();
            $table->timestamp('package_end_at')->nullable();
            $table->unsignedInteger('package_duration_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn([
            'package_name', 'package_start_at', 'package_end_at', 'package_duration_days',
        ]));
    }
};

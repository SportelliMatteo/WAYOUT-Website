<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('founder_settings')) {
            Schema::create('founder_settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('key')->unique();
                $table->unsignedInteger('value');
                $table->timestamps();
            });
        }

        foreach (config('founder.default_capacities') as $key => $value) {
            $exists = DB::table('founder_settings')->where('key', $key)->exists();

            DB::table('founder_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    ...($exists ? [] : ['id' => (string) Str::uuid(), 'created_at' => now()]),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('founder_settings');
    }
};

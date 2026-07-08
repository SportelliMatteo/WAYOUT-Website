<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('waitlist_entries')) {
            Schema::table('waitlist_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('waitlist_entries', 'phone_prefix')) {
                    $table->string('phone_prefix', 8)->nullable()->after('birth_date');
                }

                if (! Schema::hasColumn('waitlist_entries', 'phone_number')) {
                    $table->string('phone_number', 32)->nullable()->after('phone_prefix');
                }
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (! Schema::hasColumn('purchases', 'phone_prefix')) {
                    $table->string('phone_prefix', 8)->nullable()->after('birth_date');
                }

                if (! Schema::hasColumn('purchases', 'phone_number')) {
                    $table->string('phone_number', 32)->nullable()->after('phone_prefix');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('waitlist_entries')) {
            Schema::table('waitlist_entries', function (Blueprint $table) {
                foreach (['phone_number', 'phone_prefix'] as $column) {
                    if (Schema::hasColumn('waitlist_entries', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                foreach (['phone_number', 'phone_prefix'] as $column) {
                    if (Schema::hasColumn('purchases', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('waitlist_entries')) {
            return;
        }

        Schema::table('waitlist_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('waitlist_entries', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }

            if (! Schema::hasColumn('waitlist_entries', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('waitlist_entries', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('last_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('waitlist_entries')) {
            return;
        }

        Schema::table('waitlist_entries', function (Blueprint $table) {
            foreach (['birth_date', 'last_name', 'first_name'] as $column) {
                if (Schema::hasColumn('waitlist_entries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

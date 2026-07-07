<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }

            if (! Schema::hasColumn('purchases', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('purchases', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('purchases', 'invoice_requested')) {
                $table->boolean('invoice_requested')->default(false)->after('status');
            }

            if (! Schema::hasColumn('purchases', 'fiscal_code')) {
                $table->string('fiscal_code', 32)->nullable()->after('invoice_requested');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            foreach (['fiscal_code', 'invoice_requested', 'birth_date', 'last_name', 'first_name'] as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

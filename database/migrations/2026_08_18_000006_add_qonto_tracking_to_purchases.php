<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('qonto_invoice_status', 32)->nullable()->after('qonto_invoice_number');
            $table->string('qonto_einvoicing_status', 64)->nullable()->after('qonto_invoice_status');
            $table->json('qonto_einvoicing_events')->nullable()->after('qonto_einvoicing_status');
            $table->timestamp('qonto_invoice_synced_at')->nullable()->after('qonto_invoice_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'qonto_invoice_status',
                'qonto_einvoicing_status',
                'qonto_einvoicing_events',
                'qonto_invoice_synced_at',
            ]);
        });
    }
};

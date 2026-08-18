<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('billing_customer_type', 32)->nullable()->after('fiscal_code');
            $table->string('billing_address')->nullable()->after('billing_customer_type');
            $table->string('billing_postal_code', 20)->nullable()->after('billing_address');
            $table->string('billing_city', 120)->nullable()->after('billing_postal_code');
            $table->string('billing_province', 8)->nullable()->after('billing_city');
            $table->string('billing_country', 2)->nullable()->after('billing_province');
            $table->string('company_name')->nullable()->after('billing_country');
            $table->string('vat_number', 20)->nullable()->after('company_name');
            $table->string('sdi_code', 7)->nullable()->after('vat_number');
            $table->string('pec')->nullable()->after('sdi_code');
            $table->string('electronic_invoice_status', 32)->default('not_requested')->after('pec');
            $table->uuid('qonto_client_id')->nullable()->after('electronic_invoice_status');
            $table->uuid('qonto_invoice_id')->nullable()->after('qonto_client_id');
            $table->string('qonto_invoice_number', 40)->nullable()->after('qonto_invoice_id');
            $table->text('qonto_invoice_error')->nullable()->after('qonto_invoice_number');
            $table->timestamp('qonto_invoice_attempted_at')->nullable()->after('qonto_invoice_error');
            $table->timestamp('qonto_invoice_sent_at')->nullable()->after('qonto_invoice_attempted_at');
        });

        DB::table('purchases')
            ->where('invoice_requested', true)
            ->update(['electronic_invoice_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'billing_customer_type',
                'billing_address',
                'billing_postal_code',
                'billing_city',
                'billing_province',
                'billing_country',
                'company_name',
                'vat_number',
                'sdi_code',
                'pec',
                'electronic_invoice_status',
                'qonto_client_id',
                'qonto_invoice_id',
                'qonto_invoice_number',
                'qonto_invoice_error',
                'qonto_invoice_attempted_at',
                'qonto_invoice_sent_at',
            ]);
        });
    }
};

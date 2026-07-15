<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_document_versions', function (Blueprint $table) {
            $table->string('title')->nullable()->after('locale');
            $table->text('description')->nullable()->after('title');
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_key', 64);
            $table->string('locale', 10);
            $table->foreignId('current_version_id')->nullable()->constrained('legal_document_versions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');

        Schema::table('legal_document_versions', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};

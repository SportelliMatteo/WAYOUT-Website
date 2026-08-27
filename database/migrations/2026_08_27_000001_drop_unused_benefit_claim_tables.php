<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('benefit_claims');
        Schema::dropIfExists('benefit_email_challenges');
    }

    public function down(): void
    {
        // Intentionally irreversible: these legacy tables and their data are no longer used.
    }
};

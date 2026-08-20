<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('waitlist_entries')
            ->whereNotNull('nickname')
            ->where('nickname', '<>', '')
            ->where('nickname', 'not like', '@%')
            ->update([
                'nickname' => DB::raw("'@' || nickname"),
            ]);
    }

    public function down(): void
    {
        DB::table('waitlist_entries')
            ->where('nickname', 'like', '@%')
            ->update([
                'nickname' => DB::raw('substring(nickname from 2)'),
            ]);
    }
};

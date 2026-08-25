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
            ->orderBy('id')
            ->each(fn (object $entry) => DB::table('waitlist_entries')->where('id', $entry->id)->update([
                'nickname' => '@'.$entry->nickname,
            ]));
    }

    public function down(): void
    {
        DB::table('waitlist_entries')
            ->where('nickname', 'like', '@%')
            ->orderBy('id')
            ->each(fn (object $entry) => DB::table('waitlist_entries')->where('id', $entry->id)->update([
                'nickname' => mb_substr($entry->nickname, 1),
            ]));
    }
};

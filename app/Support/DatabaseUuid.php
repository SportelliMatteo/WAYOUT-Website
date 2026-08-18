<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DatabaseUuid
{
    public static function new(): string
    {
        return (string) Str::uuid();
    }

    /** @param array<string, mixed> $values */
    public static function insert(string $table, array $values): string
    {
        $id = self::new();

        DB::table($table)->insert(['id' => $id, ...$values]);

        return $id;
    }
}

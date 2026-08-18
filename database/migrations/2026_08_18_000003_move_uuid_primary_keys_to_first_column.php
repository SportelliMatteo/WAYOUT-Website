<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = true;

    /** @var list<string> */
    private array $tables = [
        'contact_messages',
        'waitlist_entries',
        'purchases',
        'founder_settings',
        'legal_document_versions',
        'consent_events',
        'legal_documents',
        'admin_users',
        'admin_audit_events',
        'withdrawal_requests',
        'withdrawal_request_events',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $foreignKeys = $this->foreignKeys();

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $this->quote($foreignKey->table_name),
                $this->quote($foreignKey->constraint_name),
            ));
        }

        foreach ($this->tables as $table) {
            if ($this->firstColumn($table) === 'id') {
                continue;
            }

            $columns = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('current_schema()'))
                ->where('table_name', $table)
                ->orderBy('ordinal_position')
                ->pluck('column_name')
                ->all();
            $constraints = $this->tableConstraints($table);
            $indexes = $this->standaloneIndexes($table);
            $oldTable = $table.'_uuid_reorder_old';
            $dataColumns = array_values(array_filter($columns, fn (string $column) => $column !== 'id'));
            $quotedDataColumns = implode(', ', array_map($this->quote(...), $dataColumns));

            DB::statement('ALTER TABLE '.$this->quote($table).' RENAME TO '.$this->quote($oldTable));
            DB::statement('ALTER TABLE '.$this->quote($oldTable).' RENAME COLUMN id TO id_uuid_reorder_old');
            DB::statement(sprintf(
                'CREATE TABLE %s (id uuid NOT NULL DEFAULT gen_random_uuid(), LIKE %s INCLUDING DEFAULTS INCLUDING GENERATED INCLUDING IDENTITY INCLUDING STORAGE INCLUDING COMMENTS)',
                $this->quote($table),
                $this->quote($oldTable),
            ));
            DB::statement(sprintf(
                'INSERT INTO %s (id, %s) SELECT id_uuid_reorder_old, %s FROM %s',
                $this->quote($table),
                $quotedDataColumns,
                $quotedDataColumns,
                $this->quote($oldTable),
            ));
            DB::statement('ALTER TABLE '.$this->quote($table).' DROP COLUMN id_uuid_reorder_old');
            DB::statement('DROP TABLE '.$this->quote($oldTable));

            foreach ($constraints as $constraint) {
                DB::statement(sprintf(
                    'ALTER TABLE %s ADD CONSTRAINT %s %s',
                    $this->quote($table),
                    $this->quote($constraint->constraint_name),
                    $constraint->definition,
                ));
            }

            foreach ($indexes as $index) {
                DB::statement($index->definition);
            }
        }

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s %s',
                $this->quote($foreignKey->table_name),
                $this->quote($foreignKey->constraint_name),
                $foreignKey->definition,
            ));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Physical column reordering is intentionally irreversible.');
    }

    private function firstColumn(string $table): ?string
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', DB::raw('current_schema()'))
            ->where('table_name', $table)
            ->orderBy('ordinal_position')
            ->value('column_name');
    }

    /** @return list<object> */
    private function foreignKeys(): array
    {
        return DB::select(<<<'SQL'
            SELECT c.conrelid::regclass::text AS table_name,
                   c.conname AS constraint_name,
                   pg_get_constraintdef(c.oid) AS definition
            FROM pg_constraint c
            JOIN pg_namespace n ON n.oid = c.connamespace
            WHERE c.contype = 'f'
              AND n.nspname = current_schema()
              AND c.conrelid::regclass::text = ANY (?::text[])
            ORDER BY c.conrelid::regclass::text, c.conname
            SQL, ['{'.implode(',', $this->tables).'}']);
    }

    /** @return list<object> */
    private function tableConstraints(string $table): array
    {
        return DB::select(<<<'SQL'
            SELECT c.conname AS constraint_name,
                   pg_get_constraintdef(c.oid) AS definition
            FROM pg_constraint c
            JOIN pg_class t ON t.oid = c.conrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            WHERE n.nspname = current_schema()
              AND t.relname = ?
              AND c.contype <> 'f'
            ORDER BY c.conname
            SQL, [$table]);
    }

    /** @return list<object> */
    private function standaloneIndexes(string $table): array
    {
        return DB::select(<<<'SQL'
            SELECT pg_get_indexdef(i.indexrelid) AS definition
            FROM pg_index i
            JOIN pg_class t ON t.oid = i.indrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            LEFT JOIN pg_constraint c ON c.conindid = i.indexrelid
            WHERE n.nspname = current_schema()
              AND t.relname = ?
              AND c.oid IS NULL
            ORDER BY i.indexrelid::regclass::text
            SQL, [$table]);
    }

    private function quote(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
};

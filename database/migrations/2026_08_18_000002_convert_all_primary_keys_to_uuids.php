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

    /** @var list<array{0: string, 1: string, 2: string, 3: string}> */
    private array $foreignKeys = [
        ['consent_events', 'waitlist_entry_id', 'waitlist_entries', 'SET NULL'],
        ['consent_events', 'purchase_id', 'purchases', 'SET NULL'],
        ['consent_events', 'contact_message_id', 'contact_messages', 'SET NULL'],
        ['consent_events', 'revokes_event_id', 'consent_events', 'SET NULL'],
        ['legal_documents', 'current_version_id', 'legal_document_versions', 'SET NULL'],
        ['admin_audit_events', 'admin_user_id', 'admin_users', 'SET NULL'],
        ['withdrawal_requests', 'purchase_id', 'purchases', 'SET NULL'],
        ['withdrawal_request_events', 'withdrawal_request_id', 'withdrawal_requests', 'CASCADE'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE purchases ADD COLUMN IF NOT EXISTS order_reference varchar(64)');

        if ($this->primaryKeysAreAlreadyUuids()) {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE purchases
            SET order_reference = 'WO-' || EXTRACT(YEAR FROM created_at)::integer || '-' || LPAD(id::text, 6, '0')
            WHERE order_reference IS NULL
            SQL);
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS purchases_order_reference_unique ON purchases (order_reference)');

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ADD COLUMN id_uuid uuid NOT NULL DEFAULT gen_random_uuid()");
        }

        foreach ($this->foreignKeys as [$table, $column, $parent]) {
            DB::statement("ALTER TABLE {$table} ADD COLUMN {$column}_uuid uuid");
            DB::statement(<<<SQL
                UPDATE {$table} child
                SET {$column}_uuid = parent.id_uuid
                FROM {$parent} parent
                WHERE child.{$column} = parent.id
                SQL);
        }

        DB::statement('ALTER TABLE admin_audit_events ADD COLUMN target_id_uuid uuid');
        DB::statement(<<<'SQL'
            UPDATE admin_audit_events event
            SET target_id_uuid = admin.id_uuid
            FROM admin_users admin
            WHERE event.target_type = 'admin_user' AND event.target_id = admin.id
            SQL);
        DB::statement(<<<'SQL'
            UPDATE admin_audit_events event
            SET target_id_uuid = version.id_uuid
            FROM legal_document_versions version
            WHERE event.target_type = 'legal_document' AND event.target_id = version.id
            SQL);

        DB::statement('ALTER TABLE withdrawal_request_events ADD COLUMN actor_id_uuid uuid');
        DB::statement(<<<'SQL'
            UPDATE withdrawal_request_events event
            SET actor_id_uuid = admin.id_uuid
            FROM admin_users admin
            WHERE event.actor_id = admin.id
            SQL);

        $this->dropForeignKeyConstraints();
        $this->dropPrimaryKeyConstraints();

        foreach ($this->foreignKeys as [$table, $column]) {
            DB::statement("ALTER TABLE {$table} DROP COLUMN {$column}");
            DB::statement("ALTER TABLE {$table} RENAME COLUMN {$column}_uuid TO {$column}");
        }

        DB::statement('ALTER TABLE withdrawal_request_events ALTER COLUMN withdrawal_request_id SET NOT NULL');

        DB::statement('ALTER TABLE admin_audit_events DROP COLUMN target_id');
        DB::statement('ALTER TABLE admin_audit_events RENAME COLUMN target_id_uuid TO target_id');
        DB::statement('ALTER TABLE withdrawal_request_events DROP COLUMN actor_id');
        DB::statement('ALTER TABLE withdrawal_request_events RENAME COLUMN actor_id_uuid TO actor_id');

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} DROP COLUMN id");
            DB::statement("ALTER TABLE {$table} RENAME COLUMN id_uuid TO id");
            DB::statement("ALTER TABLE {$table} ADD PRIMARY KEY (id)");
        }

        foreach ($this->foreignKeys as [$table, $column, $parent, $onDelete]) {
            DB::statement(<<<SQL
                ALTER TABLE {$table}
                ADD CONSTRAINT {$table}_{$column}_foreign
                FOREIGN KEY ({$column}) REFERENCES {$parent}(id)
                ON DELETE {$onDelete}
                SQL);
        }

        DB::statement('CREATE INDEX IF NOT EXISTS consent_events_waitlist_entry_id_consent_type_occurred_at_index ON consent_events (waitlist_entry_id, consent_type, occurred_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS consent_events_purchase_id_consent_type_index ON consent_events (purchase_id, consent_type)');
        DB::statement('CREATE INDEX IF NOT EXISTS admin_audit_events_target_type_target_id_index ON admin_audit_events (target_type, target_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS withdrawal_request_events_withdrawal_request_id_occurred_at_index ON withdrawal_request_events (withdrawal_request_id, occurred_at)');
    }

    public function down(): void
    {
        throw new RuntimeException('The UUID primary-key migration is intentionally irreversible.');
    }

    private function primaryKeysAreAlreadyUuids(): bool
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', DB::raw('current_schema()'))
            ->where('table_name', 'waitlist_entries')
            ->where('column_name', 'id')
            ->value('data_type') === 'uuid';
    }

    private function dropForeignKeyConstraints(): void
    {
        $constraints = DB::select(<<<'SQL'
            SELECT c.conname, c.conrelid::regclass::text AS table_name
            FROM pg_constraint c
            JOIN pg_namespace n ON n.oid = c.connamespace
            WHERE c.contype = 'f'
              AND n.nspname = current_schema()
              AND c.conrelid::regclass::text = ANY (?::text[])
            SQL, ['{'.implode(',', $this->tables).'}']);

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $this->quoteIdentifier($constraint->table_name),
                $this->quoteIdentifier($constraint->conname),
            ));
        }
    }

    private function dropPrimaryKeyConstraints(): void
    {
        $constraints = DB::select(<<<'SQL'
            SELECT c.conname, c.conrelid::regclass::text AS table_name
            FROM pg_constraint c
            JOIN pg_namespace n ON n.oid = c.connamespace
            WHERE c.contype = 'p'
              AND n.nspname = current_schema()
              AND c.conrelid::regclass::text = ANY (?::text[])
            SQL, ['{'.implode(',', $this->tables).'}']);

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $this->quoteIdentifier($constraint->table_name),
                $this->quoteIdentifier($constraint->conname),
            ));
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
};

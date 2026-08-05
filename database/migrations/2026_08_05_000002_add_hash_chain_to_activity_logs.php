<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('previous_hash')->nullable()->after('trace_id');
            $table->string('hash')->nullable()->after('previous_hash');
        });

        // Computed at the DB level (not in PHP/Eloquent) for two reasons:
        // 1. It has to cover every insert path, including the 19 files that
        //    write ActivityLog::create() directly rather than through
        //    AuditLogger — a trigger is the only place all of them pass through.
        // 2. Concurrency safety. A naive "read last hash, then insert" done in
        //    PHP has a race: two concurrent requests can both read the same
        //    last hash and each compute a hash chained to it, forking the
        //    chain. pg_advisory_xact_lock serializes concurrent inserts on a
        //    fixed lock key (not a row lock, so no phantom-read risk from
        //    ORDER BY ... LIMIT 1 FOR UPDATE) and auto-releases at commit.
        DB::statement("
            CREATE OR REPLACE FUNCTION activity_logs_hash_chain() RETURNS TRIGGER AS $$
            DECLARE
                last_hash TEXT;
                row_text TEXT;
            BEGIN
                PERFORM pg_advisory_xact_lock(hashtext('activity_logs_hash_chain'));

                SELECT hash INTO last_hash FROM activity_logs ORDER BY id DESC LIMIT 1;

                row_text := COALESCE(NEW.action, '') || '|' ||
                            COALESCE(NEW.entity_type, '') || '|' ||
                            COALESCE(NEW.entity_id::text, '') || '|' ||
                            COALESCE(NEW.user_id::text, '') || '|' ||
                            COALESCE(NEW.entity_identifier, '') || '|' ||
                            COALESCE(NEW.old_values::text, '') || '|' ||
                            COALESCE(NEW.new_values::text, '') || '|' ||
                            COALESCE(NEW.status::text, '') || '|' ||
                            COALESCE(NEW.severity::text, '') || '|' ||
                            COALESCE(NEW.created_at::text, now()::text);

                NEW.previous_hash := last_hash;
                NEW.hash := encode(digest(COALESCE(last_hash, '') || row_text, 'sha256'), 'hex');

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER activity_logs_compute_hash
            BEFORE INSERT ON activity_logs
            FOR EACH ROW EXECUTE FUNCTION activity_logs_hash_chain();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_compute_hash ON activity_logs');
        DB::statement('DROP FUNCTION IF EXISTS activity_logs_hash_chain()');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['previous_hash', 'hash']);
        });

        DB::statement('DROP EXTENSION IF EXISTS pgcrypto');
    }
};

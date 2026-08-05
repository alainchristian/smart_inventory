<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New enum types for this table only (audit_status has no existing equivalent;
        // severity reuses the app's existing alert_severity enum — same three values).
        DB::statement("DO $$ BEGIN CREATE TYPE audit_actor_type AS ENUM ('user', 'system', 'api'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE audit_status AS ENUM ('success', 'failed'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('module', 100)->nullable()->after('action');
            $table->string('actor_role_snapshot')->nullable()->after('user_name');
            $table->string('session_id')->nullable()->after('ip_address');
            $table->string('device_fingerprint')->nullable()->after('session_id');
            $table->uuid('trace_id')->nullable()->after('device_fingerprint');
        });

        // Native Postgres enum columns — Blueprint has no first-class support for
        // custom enum types, so these are added via raw SQL (matches the pattern
        // already used for boxes.status / boxes.location_type in this codebase).
        DB::statement("ALTER TABLE activity_logs ADD COLUMN actor_type audit_actor_type NOT NULL DEFAULT 'user'");
        DB::statement("ALTER TABLE activity_logs ADD COLUMN status audit_status NOT NULL DEFAULT 'success'");
        DB::statement("ALTER TABLE activity_logs ADD COLUMN severity alert_severity NOT NULL DEFAULT 'info'");

        // Upgrade json -> jsonb (supports indexing/containment queries; existing rows cast cleanly)
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN old_values TYPE jsonb USING old_values::jsonb');
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN new_values TYPE jsonb USING new_values::jsonb');

        // Upgrade ip_address varchar -> native inet (existing values are all valid IPs from request()->ip())
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN ip_address TYPE inet USING NULLIF(ip_address, \'\')::inet');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['module', 'action']);
            $table->index('severity');
            $table->index('status');
            $table->index('trace_id');
        });

        // Enforce insert-only at the database level. The app connects as the table
        // owner, so REVOKE UPDATE/DELETE would have no effect (owners bypass grants) —
        // a trigger is the only mechanism Postgres offers that actually blocks this
        // regardless of role.
        DB::statement("
            CREATE OR REPLACE FUNCTION prevent_activity_logs_mutation() RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION 'activity_logs is append-only: % is not permitted', TG_OP;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER activity_logs_no_update
            BEFORE UPDATE ON activity_logs
            FOR EACH ROW EXECUTE FUNCTION prevent_activity_logs_mutation();
        ");
        DB::statement("
            CREATE TRIGGER activity_logs_no_delete
            BEFORE DELETE ON activity_logs
            FOR EACH ROW EXECUTE FUNCTION prevent_activity_logs_mutation();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_no_update ON activity_logs');
        DB::statement('DROP TRIGGER IF EXISTS activity_logs_no_delete ON activity_logs');
        DB::statement('DROP FUNCTION IF EXISTS prevent_activity_logs_mutation()');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['module', 'action']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['status']);
            $table->dropIndex(['trace_id']);
        });

        DB::statement('ALTER TABLE activity_logs ALTER COLUMN ip_address TYPE varchar(45) USING ip_address::varchar');
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN old_values TYPE json USING old_values::json');
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN new_values TYPE json USING new_values::json');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['actor_type', 'status', 'severity', 'module', 'actor_role_snapshot', 'session_id', 'device_fingerprint', 'trace_id']);
        });

        DB::statement('DROP TYPE IF EXISTS audit_actor_type');
        DB::statement('DROP TYPE IF EXISTS audit_status');
    }
};

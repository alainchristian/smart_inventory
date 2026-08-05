<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Walk the activity_logs hash chain and report the first row (if any) where the hash no longer matches — proof the chain has not been tampered with or had rows removed out of sequence';

    /**
     * The verification query recomputes each row's expected hash from the
     * ACTUAL previous row's stored hash (via LAG()) using the identical
     * expression the activity_logs_compute_hash trigger uses, then compares
     * against what's stored. Deliberately done in SQL, not PHP: Postgres's
     * jsonb::text cast and PHP's json_encode() are not guaranteed to produce
     * byte-identical output, so recomputing in PHP would risk false-positive
     * "tampered" reports on every single row.
     */
    private const VERIFY_SQL = <<<'SQL'
        WITH chain AS (
            SELECT
                id,
                created_at,
                hash,
                previous_hash,
                LAG(hash) OVER (ORDER BY id) AS actual_previous_hash,
                encode(
                    digest(
                        COALESCE(LAG(hash) OVER (ORDER BY id), '') ||
                        COALESCE(action, '') || '|' ||
                        COALESCE(entity_type, '') || '|' ||
                        COALESCE(entity_id::text, '') || '|' ||
                        COALESCE(user_id::text, '') || '|' ||
                        COALESCE(entity_identifier, '') || '|' ||
                        COALESCE(old_values::text, '') || '|' ||
                        COALESCE(new_values::text, '') || '|' ||
                        COALESCE(status::text, '') || '|' ||
                        COALESCE(severity::text, '') || '|' ||
                        COALESCE(created_at::text, ''),
                        'sha256'
                    ),
                    'hex'
                ) AS expected_hash
            FROM activity_logs
            WHERE hash IS NOT NULL
        )
        SELECT id, created_at, hash, previous_hash, actual_previous_hash, expected_hash
        FROM chain
        WHERE hash != expected_hash
           OR previous_hash IS DISTINCT FROM actual_previous_hash
        ORDER BY id
        LIMIT 1
    SQL;

    public function handle(): int
    {
        $totalRows = DB::table('activity_logs')->count();
        $unhashedCount = DB::table('activity_logs')->whereNull('hash')->count();
        $hashedCount = $totalRows - $unhashedCount;

        $this->info("activity_logs: {$totalRows} total rows.");

        if ($unhashedCount > 0) {
            $this->comment("{$unhashedCount} row(s) predate hash-chaining (created before this feature shipped) and are not covered by this check.");
        }

        if ($hashedCount === 0) {
            $this->comment('No hash-chained rows to verify yet.');
            return self::SUCCESS;
        }

        $break = DB::select(self::VERIFY_SQL);

        if (empty($break)) {
            $this->info("✓ Chain verified intact — all {$hashedCount} hash-chained rows check out. No tampering or missing rows detected.");
            return self::SUCCESS;
        }

        $row = $break[0];

        $this->error("✗ Chain integrity broken at row id={$row->id} (created_at={$row->created_at}).");

        if ($row->hash !== $row->expected_hash) {
            $this->line("  Stored hash does not match the recomputed hash for this row's own content.");
            $this->line("  Stored:    {$row->hash}");
            $this->line("  Expected:  {$row->expected_hash}");
        }

        if ($row->previous_hash !== $row->actual_previous_hash) {
            $this->line("  Stored previous_hash does not match the hash actually stored on the preceding row.");
            $this->line("  Stored previous_hash: " . ($row->previous_hash ?? 'NULL'));
            $this->line("  Actual previous hash: " . ($row->actual_previous_hash ?? 'NULL'));
            $this->line('  This usually means a row was deleted or reordered out of sequence.');
        }

        return self::FAILURE;
    }
}

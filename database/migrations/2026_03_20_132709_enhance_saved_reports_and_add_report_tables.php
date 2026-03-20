<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Add caching + scheduling + pinning to saved_reports ───────────
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->jsonb('last_results')->nullable()->after('config');
            $table->timestamp('results_cached_at')->nullable()->after('last_results');
            $table->timestamp('results_stale_at')->nullable()->after('results_cached_at');
            $table->string('schedule_cron', 100)->nullable()->after('results_stale_at');
            $table->jsonb('schedule_recipients')->nullable()->after('schedule_cron');
            $table->timestamp('last_scheduled_run_at')->nullable()->after('schedule_recipients');
            $table->boolean('pinned_to_dashboard')->default(false)->after('last_scheduled_run_at');
            $table->unsignedTinyInteger('dashboard_position')->nullable()->after('pinned_to_dashboard');
        });

        // ── 2. report_run_history ─────────────────────────────────────────────
        Schema::create('report_run_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('run_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('run_at');
            $table->jsonb('config_snapshot');          // snapshot of config at run time
            $table->jsonb('results')->nullable();      // full results array
            $table->unsignedInteger('duration_ms')->default(0);
            $table->boolean('was_scheduled')->default(false);
            $table->timestamps();

            $table->index(['report_id', 'run_at']);
        });

        // ── 3. report_annotations ────────────────────────────────────────────
        Schema::create('report_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('run_history_id')->nullable()->constrained('report_run_history')->nullOnDelete();
            $table->string('block_id', 60)->nullable(); // null = report-level annotation
            $table->text('note');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['report_id', 'run_history_id']);
        });

        // ── 4. report_view_log ────────────────────────────────────────────────
        Schema::create('report_view_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('viewed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->boolean('was_run')->default(false);

            $table->index(['report_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_view_log');
        Schema::dropIfExists('report_annotations');
        Schema::dropIfExists('report_run_history');
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->dropColumn([
                'last_results', 'results_cached_at', 'results_stale_at',
                'schedule_cron', 'schedule_recipients', 'last_scheduled_run_at',
                'pinned_to_dashboard', 'dashboard_position',
            ]);
        });
    }
};

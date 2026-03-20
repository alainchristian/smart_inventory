<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Config JSONB schema:
 * {
 *   "date_range": "month",        // today|week|month|quarter|year|custom
 *   "date_from": null,            // Y-m-d string, used when date_range = custom
 *   "date_to": null,              // Y-m-d string, used when date_range = custom
 *   "location_filter": "all",     // all|shop:ID|warehouse:ID
 *   "blocks": [
 *     {
 *       "id": "b1",               // unique within this report
 *       "metric_id": "sales_revenue",
 *       "title": "Total Revenue",
 *       "width": "half",          // half|full
 *       "viz": "kpi_card",        // kpi_card|table|bar_chart|line_chart
 *       "position": 0
 *     }
 *   ]
 * }
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_shared')->default(false);   // shared = visible to all owner/admin users
            $table->jsonb('config');                         // full report definition (see schema above)
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_by', 'deleted_at']);
            $table->index(['is_shared', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};

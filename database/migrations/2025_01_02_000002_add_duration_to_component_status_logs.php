<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('component_status_logs', function (Blueprint $table) {
            // Add duration column for pre-calculated durations between status changes
            // This eliminates need for runtime diffInMinutes() calculations
            // Significantly speeds up uptime and analytics calculations
            $table->unsignedInteger('duration_minutes')->nullable()->after('new_status');
            
            // Add index for aggregation queries
            // Query pattern: SUM(duration_minutes) WHERE component_id = ? AND new_status = ?
            $table->index(['component_id', 'new_status', 'duration_minutes'], 'idx_status_logs_duration_agg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('component_status_logs', function (Blueprint $table) {
            $table->dropIndex('idx_status_logs_duration_agg');
            $table->dropColumn('duration_minutes');
        });
    }
};

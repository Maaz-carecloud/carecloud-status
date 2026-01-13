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
            // Most critical: Timeline queries for analytics and uptime calculation
            // Used in: get90DayStatusTimeline(), calculateUptime(), getStatusHistory()
            $table->index(['component_id', 'created_at'], 'idx_status_logs_component_date');
            
            // Status transition analysis and reporting
            // Used in: Analytics queries, dashboard statistics
            $table->index(['new_status', 'created_at'], 'idx_status_logs_status_date');
            
            // Incident-related status changes
            // Used in: Tracking status changes caused by incidents
            $table->index(['incident_id', 'created_at'], 'idx_status_logs_incident');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('component_status_logs', function (Blueprint $table) {
            $table->dropIndex('idx_status_logs_component_date');
            $table->dropIndex('idx_status_logs_status_date');
            $table->dropIndex('idx_status_logs_incident');
        });
    }
};

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
        Schema::table('incidents', function (Blueprint $table) {
            // Filter by status and date for active/recent incidents
            // Used in: Incident::active(), getActiveIncidents(), recent() scope
            $table->index(['status', 'created_at'], 'idx_incidents_status_created');
            
            // Scheduled maintenance queries
            // Used in: Incident::scheduled(), StatusPage component
            $table->index(['is_scheduled', 'scheduled_at'], 'idx_incidents_scheduled');
            
            // Resolved incidents with resolution time
            // Used in: Incident::resolved(), IncidentHistory component
            $table->index(['status', 'resolved_at'], 'idx_incidents_resolved');
            
            // User's incident history
            // Used in: User relationship queries, IncidentList filtering
            $table->index(['user_id', 'created_at'], 'idx_incidents_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_status_created');
            $table->dropIndex('idx_incidents_scheduled');
            $table->dropIndex('idx_incidents_resolved');
            $table->dropIndex('idx_incidents_user_created');
        });
    }
};

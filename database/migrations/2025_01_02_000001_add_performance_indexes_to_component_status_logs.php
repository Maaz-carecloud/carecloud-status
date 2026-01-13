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
            // Check and add covering index for uptime calculations
            if (!$this->indexExists('component_status_logs', 'idx_status_logs_uptime_calc')) {
                $table->index(['component_id', 'created_at', 'new_status', 'old_status'], 'idx_status_logs_uptime_calc');
            }
            
            // Check and add multi-column index for date range queries
            if (!$this->indexExists('component_status_logs', 'idx_status_logs_date_range')) {
                $table->index(['created_at', 'component_id', 'new_status'], 'idx_status_logs_date_range');
            }
            
            // Check and add index for incident impact analysis
            if (!$this->indexExists('component_status_logs', 'idx_status_logs_incident_status')) {
                $table->index(['incident_id', 'new_status', 'created_at'], 'idx_status_logs_incident_status');
            }
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $index): bool
    {
        $database = config('database.connections.mysql.database');
        
        $result = DB::selectOne(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$database, $table, $index]
        );
        
        return $result->count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('component_status_logs', function (Blueprint $table) {
            if ($this->indexExists('component_status_logs', 'idx_status_logs_uptime_calc')) {
                $table->dropIndex('idx_status_logs_uptime_calc');
            }
            if ($this->indexExists('component_status_logs', 'idx_status_logs_date_range')) {
                $table->dropIndex('idx_status_logs_date_range');
            }
            if ($this->indexExists('component_status_logs', 'idx_status_logs_incident_status')) {
                $table->dropIndex('idx_status_logs_incident_status');
            }
        });
    }
};

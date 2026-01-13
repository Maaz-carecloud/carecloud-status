<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the components table enum to use 'under_maintenance' instead of 'maintenance'
        DB::statement("ALTER TABLE `components` MODIFY COLUMN `status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance') NOT NULL DEFAULT 'operational'");

        // Update existing records that have 'maintenance' to 'under_maintenance'
        DB::table('components')
            ->where('status', 'maintenance')
            ->update(['status' => 'under_maintenance']);

        // Update the component_status_logs table enum
        DB::statement("ALTER TABLE `component_status_logs` MODIFY COLUMN `old_status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance') NOT NULL");
        DB::statement("ALTER TABLE `component_status_logs` MODIFY COLUMN `new_status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance') NOT NULL");

        // Update existing log records
        DB::table('component_status_logs')
            ->where('old_status', 'maintenance')
            ->update(['old_status' => 'under_maintenance']);
        
        DB::table('component_status_logs')
            ->where('new_status', 'maintenance')
            ->update(['new_status' => 'under_maintenance']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'maintenance'
        DB::table('components')
            ->where('status', 'under_maintenance')
            ->update(['status' => 'maintenance']);

        DB::statement("ALTER TABLE `components` MODIFY COLUMN `status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance') NOT NULL DEFAULT 'operational'");

        // Revert log records
        DB::table('component_status_logs')
            ->where('old_status', 'under_maintenance')
            ->update(['old_status' => 'maintenance']);
        
        DB::table('component_status_logs')
            ->where('new_status', 'under_maintenance')
            ->update(['new_status' => 'maintenance']);

        DB::statement("ALTER TABLE `component_status_logs` MODIFY COLUMN `old_status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance') NOT NULL");
        DB::statement("ALTER TABLE `component_status_logs` MODIFY COLUMN `new_status` ENUM('operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance') NOT NULL");
    }
};

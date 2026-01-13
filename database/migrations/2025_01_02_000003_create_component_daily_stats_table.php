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
        Schema::create('component_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // Pre-aggregated time spent in each status (in minutes)
            // Total should equal 1440 minutes (24 hours)
            $table->unsignedSmallInteger('operational_minutes')->default(0);
            $table->unsignedSmallInteger('degraded_minutes')->default(0);
            $table->unsignedSmallInteger('partial_outage_minutes')->default(0);
            $table->unsignedSmallInteger('major_outage_minutes')->default(0);
            $table->unsignedSmallInteger('maintenance_minutes')->default(0);
            
            // Pre-calculated uptime percentage for the day
            $table->decimal('uptime_percentage', 5, 2)->default(100.00);
            
            // Number of status changes during the day
            $table->unsignedSmallInteger('status_changes')->default(0);
            
            // Most severe status reached during the day
            $table->enum('worst_status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance'])->default('operational');
            
            $table->timestamps();
            
            // Unique constraint: one record per component per day
            $table->unique(['component_id', 'date'], 'idx_daily_stats_component_date');
            
            // Index for date range queries
            $table->index(['date', 'component_id'], 'idx_daily_stats_date_range');
            
            // Index for uptime analysis
            $table->index(['component_id', 'date', 'uptime_percentage'], 'idx_daily_stats_uptime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_daily_stats');
    }
};

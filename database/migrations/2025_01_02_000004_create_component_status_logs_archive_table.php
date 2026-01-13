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
        Schema::create('component_status_logs_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id'); // Not a foreign key in archive
            $table->enum('old_status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance']);
            $table->enum('new_status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'under_maintenance']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('incident_id')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();
            
            // Indexes for archive queries
            $table->index(['component_id', 'created_at'], 'idx_archive_component_date');
            $table->index('created_at', 'idx_archive_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_status_logs_archive');
    }
};

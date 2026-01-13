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
        Schema::table('incident_updates', function (Blueprint $table) {
            // Incident timeline - most recent updates first
            // Used in: IncidentUpdates component, incident detail pages
            // Note: Using raw query to create DESC index
            \DB::statement('CREATE INDEX idx_incident_updates_incident_date ON incident_updates (incident_id, created_at DESC)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_updates', function (Blueprint $table) {
            $table->dropIndex('idx_incident_updates_incident_date');
        });
    }
};

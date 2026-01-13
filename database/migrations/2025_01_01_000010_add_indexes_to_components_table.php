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
        Schema::table('components', function (Blueprint $table) {
            // Query optimization for enabled, ordered components
            // Used in: Component::enabled()->ordered()->get()
            $table->index(['is_enabled', 'order'], 'idx_components_enabled_order');
            
            // Filter by status and enabled state
            // Used in: ComponentStatusService::getComponentsByStatus()
            $table->index(['status', 'is_enabled'], 'idx_components_status_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropIndex('idx_components_enabled_order');
            $table->dropIndex('idx_components_status_enabled');
        });
    }
};

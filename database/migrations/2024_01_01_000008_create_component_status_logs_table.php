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
        Schema::create('component_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->enum('old_status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance']);
            $table->enum('new_status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance']);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('component_id');
            $table->index('created_at');
            $table->index(['component_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_status_logs');
    }
};

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
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['operational', 'degraded_performance', 'partial_outage', 'major_outage', 'maintenance'])->default('operational');
            $table->integer('order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedBigInteger('group_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('is_enabled');
            $table->index(['order', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};

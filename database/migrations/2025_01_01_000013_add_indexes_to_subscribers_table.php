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
        Schema::table('subscribers', function (Blueprint $table) {
            // Active verified subscribers (most common query)
            // Used in: getSubscribersForComponents(), notification queries
            $table->index(['is_active', 'verified_at'], 'idx_subscribers_active_verified');
            
            // Email lookups with active filter
            // Used in: Subscriber lookup, duplicate detection
            $table->index(['email', 'is_active'], 'idx_subscribers_email_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropIndex('idx_subscribers_active_verified');
            $table->dropIndex('idx_subscribers_email_active');
        });
    }
};

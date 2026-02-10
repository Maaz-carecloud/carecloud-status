<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert or update the about_us setting
        DB::table('settings')->updateOrInsert(
            ['key' => 'about_us'],
            ['value' => "Welcome to CareCloud's home for real-time information on system performance. Here you'll find live and historical data on system performance. If there are any interruptions in service, a note will be posted here.\n\nPlease contact CareCloud's support team at (866) 931-3832 or email us at support@carecloud.com for any additional questions or concerns."]
        );
    }

    public function down(): void
    {
        // Remove the about_us setting
        DB::table('settings')->where('key', 'about_us')->delete();
    }
};

<?php

namespace App\Jobs;

use App\Services\ComponentStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogComponentStatusSnapshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 
     * Logs a daily snapshot of all component statuses for historical tracking.
     */
    public function handle(ComponentStatusService $componentStatusService): void
    {
        $count = $componentStatusService->logDailyStatusSnapshot();
        
        Log::info("Daily component status snapshot logged: {$count} components");
    }
}

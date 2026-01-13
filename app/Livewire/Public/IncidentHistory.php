<?php

namespace App\Livewire\Public;

use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Incident History')]
#[Layout('components.layouts.public')]
class IncidentHistory extends Component
{
    use WithPagination;

    #[Url]
    public $days = 30;

    #[Url]
    public $filter = 'all'; // all, resolved, ongoing

    public function render()
    {
        $startDate = Carbon::now()->subDays($this->days);
        $endDate = Carbon::now();

        // Build query with filters
        $query = Incident::query()
            ->with(['components', 'updates' => function ($query) {
                $query->latest()->limit(1);
            }, 'user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply status filter
        if ($this->filter === 'resolved') {
            $query->resolved();
        } elseif ($this->filter === 'ongoing') {
            $query->active();
        }

        // Cache count for performance
        $cacheKey = "incident_history_{$this->days}_{$this->filter}_count";
        $totalIncidents = Cache::remember($cacheKey, 300, function () use ($query) {
            return $query->count();
        });

        $incidents = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate summary statistics
        $stats = $this->calculateStats($startDate, $endDate);

        return view('livewire.public.incident-history', [
            'incidents' => $incidents,
            'totalIncidents' => $totalIncidents,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate incident statistics for the time period.
     */
    protected function calculateStats($startDate, $endDate): array
    {
        $cacheKey = "incident_history_stats_{$this->days}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $totalIncidents = Incident::whereBetween('created_at', [$startDate, $endDate])->count();
            $resolvedIncidents = Incident::resolved()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $activeIncidents = Incident::active()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Calculate average resolution time for resolved incidents
            $avgResolutionMinutes = Incident::resolved()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('started_at')
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, resolved_at)) as avg_time')
                ->value('avg_time');

            return [
                'total' => $totalIncidents,
                'resolved' => $resolvedIncidents,
                'active' => $activeIncidents,
                'avg_resolution_time' => $avgResolutionMinutes ? round($avgResolutionMinutes) : null,
            ];
        });
    }

    /**
     * Update the days filter.
     */
    public function setDays(int $days): void
    {
        $this->days = $days;
        $this->resetPage();
    }

    /**
     * Update the status filter.
     */
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    /**
     * Reset all filters.
     */
    public function resetFilters(): void
    {
        $this->days = 30;
        $this->filter = 'all';
        $this->resetPage();
    }
}

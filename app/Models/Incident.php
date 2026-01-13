<?php

namespace App\Models;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message',
        'status',
        'impact',
        'is_scheduled',
        'scheduled_at',
        'started_at',
        'resolved_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
            'impact' => IncidentImpact::class,
            'is_scheduled' => 'boolean',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the components affected by this incident.
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class)
            ->withTimestamps();
    }

    /**
     * Get the updates for this incident.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include active incidents.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [IncidentStatus::RESOLVED]);
    }

    /**
     * Scope a query to only include resolved incidents.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', IncidentStatus::RESOLVED);
    }

    /**
     * Scope a query to only include scheduled maintenance.
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    /**
     * Scope a query to order incidents by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the incident is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === IncidentStatus::RESOLVED;
    }

    /**
     * Check if the incident is scheduled maintenance.
     */
    public function isScheduledMaintenance(): bool
    {
        return $this->is_scheduled;
    }
}

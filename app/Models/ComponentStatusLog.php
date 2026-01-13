<?php

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'old_status',
        'new_status',
        'user_id',
        'incident_id',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => ComponentStatus::class,
            'new_status' => ComponentStatus::class,
        ];
    }

    /**
     * Get the component that owns this status log.
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * Get the user who made this status change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the incident associated with this status change.
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Scope a query to order logs by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

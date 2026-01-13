<?php

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'order',
        'is_enabled',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ComponentStatus::class,
            'is_enabled' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Get the incidents affecting this component.
     */
    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(Incident::class)
            ->withTimestamps();
    }

    /**
     * Get the subscribers for this component.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class)
            ->withTimestamps();
    }

    /**
     * Get the status logs for this component.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ComponentStatusLog::class);
    }

    /**
     * Scope a query to only include enabled components.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to order components by their order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

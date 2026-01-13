<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Subscriber extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'phone',
        'teams_webhook_url',
        'verification_token',
        'verified_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the components this subscriber is subscribed to.
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class)
            ->withTimestamps();
    }

    /**
     * Scope a query to only include verified subscribers.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope a query to only include active subscribers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the subscriber is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}

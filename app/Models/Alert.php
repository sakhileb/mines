<?php

namespace App\Models;

use App\Services\QueryCacheService;
use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alert Model
 * 
 * Represents system alerts triggered by rules
 * Can be about machines, maintenance, fuel, or custom conditions
 */
class Alert extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'machine_id',
        'mine_area_id',
        'type', // engine, fuel, maintenance, geofence, temperature, area, etc.
        'title',
        'description',
        'priority', // critical, high, medium, low
        'status', // active, acknowledged, resolved
        'triggered_at',
        'acknowledged_at',
        'resolved_at',
        'acknowledged_by',
        'resolved_by',
        'metadata', // JSON for rule details
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Invalidate cache when alert is created, updated, or deleted
        static::saved(function (Alert $alert) {
            QueryCacheService::invalidateAlerts($alert->team_id);
        });

        static::deleted(function (Alert $alert) {
            QueryCacheService::invalidateAlerts($alert->team_id);
        });
    }

    /**
     * Get the machine this alert is about
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the mine area this alert is about
     */
    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    /**
     * Get the team this alert belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who acknowledged this alert
     */
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get the user who resolved this alert
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Acknowledge this alert
     */
    public function acknowledge($userId = null)
    {
        return $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Resolve this alert
     */
    public function resolve($userId = null)
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Scope to active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }
}

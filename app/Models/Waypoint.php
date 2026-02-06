<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Waypoint Model
 * 
 * Represents a point along a route
 * Waypoints are ordered and define the path a machine should follow
 */
class Waypoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'sequence_order',
        'latitude',
        'longitude',
        'waypoint_type',
        'name',
        'notes',
        'estimated_time_from_previous',
        'distance_from_previous',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'estimated_time_from_previous' => 'integer',
        'distance_from_previous' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the route that owns the waypoint.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get icon for waypoint type
     */
    public function getIconAttribute(): string
    {
        return match($this->waypoint_type) {
            'fuel_station' => '⛽',
            'loading_point' => '📦',
            'dump_point' => '🚮',
            'geofence' => '🚧',
            default => '📍',
        };
    }

    /**
     * Get color for waypoint type
     */
    public function getColorAttribute(): string
    {
        return match($this->waypoint_type) {
            'fuel_station' => 'yellow',
            'loading_point' => 'blue',
            'dump_point' => 'red',
            'geofence' => 'orange',
            default => 'gray',
        };
    }
}

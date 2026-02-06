<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GeofenceEntry Model
 * 
 * Records machine entry and exit times from geofenced areas
 * Tracks tonnage and material movement
 */
class GeofenceEntry extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'geofence_id',
        'machine_id',
        'entry_time',
        'exit_time',
        'entry_latitude',
        'entry_longitude',
        'exit_latitude',
        'exit_longitude',
        'tonnage_loaded',
        'material_type',
        'notes',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'entry_latitude' => 'float',
        'entry_longitude' => 'float',
        'exit_latitude' => 'float',
        'exit_longitude' => 'float',
        'tonnage_loaded' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the machine for this entry
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the geofence for this entry
     */
    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }

    /**
     * Get the team for this entry
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Calculate duration in minutes
     */
    public function getDurationMinutes()
    {
        if (!$this->exit_time) {
            return null;
        }

        return $this->exit_time->diffInMinutes($this->entry_time);
    }

    /**
     * Get duration formatted as HH:MM
     */
    public function getFormattedDuration()
    {
        $minutes = $this->getDurationMinutes();
        if (!$minutes) {
            return 'Active';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}

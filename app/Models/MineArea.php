<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MineArea extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'location',
        'latitude',
        'longitude',
        'area_size_hectares',
        'status',
        'manager_name',
        'manager_contact',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'area_size_hectares' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Get the team this mine area belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get machines assigned to this mine area
     */
    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    /**
     * Get geofences in this mine area
     */
    public function geofences(): HasMany
    {
        return $this->hasMany(Geofence::class);
    }

    /**
     * Get alerts for this mine area
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get production records for this mine area
     */
    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    /**
     * Get production targets for this mine area
     */
    public function productionTargets(): HasMany
    {
        return $this->hasMany(ProductionTarget::class);
    }

    /**
     * Get production forecasts for this mine area
     */
    public function productionForecasts(): HasMany
    {
        return $this->hasMany(ProductionForecast::class);
    }

    /**
     * Get mine plan uploads for this mine area
     */
    public function minePlanUploads(): HasMany
    {
        return $this->hasMany(MinePlanUpload::class);
    }

    /**
     * Get routes in this mine area
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get assignment history for this mine area
     */
    public function machineAssignments(): HasMany
    {
        return $this->hasMany(MachineAreaAssignment::class);
    }

    /**
     * Scope to filter by team
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if mine area is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

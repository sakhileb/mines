<?php

namespace App\Models;

use App\Services\QueryCacheService;
use App\Traits\HasTeamFilters;
use Illuminate\Validation\ValidationException;
use App\Models\MineArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Machine Model
 * 
 * Represents a mining machine (Volvo, CAT, Komatsu, Bell truck, etc.)
 * Tracks metadata, status, and integrations with manufacturer systems
 */
class Machine extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'name',
        'machine_type', // volvo, cat, komatsu, bell, ldv
        'manufacturer',
        'model',
        'year_of_manufacture',
        'registration_number',
        'serial_number',
        'manufacturer_id', // ID from manufacturer system
        'capacity', // in tonnes
        'fuel_capacity', // in litres
        'hours_meter', // total hours
        'status', // active, idle, maintenance, offline
        'last_location_latitude',
        'last_location_longitude',
        'last_location_update',
        'integration_id',
        'mine_area_id', // Current mine area assignment
        'excavator_id', // Assigned excavator
        'assigned_to_excavator_at',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'float',
        'fuel_capacity' => 'float',
        'hours_meter' => 'float',
        'last_location_latitude' => 'float',
        'last_location_longitude' => 'float',
        'last_location_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Invalidate cache when machine is created, updated, or deleted
        static::saved(function (Machine $machine) {
            QueryCacheService::invalidateMachine($machine->id, $machine->team_id);
            QueryCacheService::invalidateDashboard($machine->team_id);
        });

        static::deleted(function (Machine $machine) {
            QueryCacheService::invalidateMachine($machine->id, $machine->team_id);
            QueryCacheService::invalidateDashboard($machine->team_id);
        });
    }

    /**
     * Get the team that owns this machine
            // Ensure machines are always assigned to a mine area when possible
            static::saving(function (Machine $machine) {
                // If mine_area_id is null, and the team has at least one active mine area, prevent save
                if (is_null($machine->mine_area_id) && $machine->team_id) {
                    $teamId = $machine->team_id;
                    $hasActive = MineArea::where('team_id', $teamId)->where('status', 'active')->exists();
                    if ($hasActive) {
                        throw ValidationException::withMessages(['mine_area_id' => 'Machine must be assigned to an active mine area for this team.']);
                    }
                }
            });
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the integration this machine belongs to
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Get all metrics for this machine
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(MachineMetric::class);
    }

    /**
     * Get all alerts for this machine
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get all geofence entries for this machine
     */
    public function geofenceEntries(): HasMany
    {
        return $this->hasMany(GeofenceEntry::class);
    }

    /**
     * Get the mine area this machine is assigned to
     */
    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    /**
     * Get assignment history for this machine
     */
    public function areaAssignments(): HasMany
    {
        return $this->hasMany(MachineAreaAssignment::class);
    }

    /**
     * Get the excavator this machine is assigned to
     */
    public function excavator(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'excavator_id');
    }

    /**
     * Get all machines assigned to this excavator
     */
    public function assignedMachines(): HasMany
    {
        return $this->hasMany(Machine::class, 'excavator_id');
    }

    /**
     * Get all maintenance records for this machine
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /**
     * Get the health status for this machine
     */
    public function healthStatus(): HasOne
    {
        return $this->hasOne(MachineHealthStatus::class);
    }

    /**
     * Assign this machine to an excavator
     */
    public function assignToExcavator($excavatorId): void
    {
        $this->update([
            'excavator_id' => $excavatorId,
            'assigned_to_excavator_at' => now(),
        ]);
    }

    /**
     * Unassign this machine from its excavator
     */
    public function unassignFromExcavator(): void
    {
        $this->update([
            'excavator_id' => null,
            'assigned_to_excavator_at' => null,
        ]);
    }

    /**
     * Get active alerts for this machine
     */
    public function activeAlerts()
    {
        return $this->alerts()->where('status', 'active');
    }

    /**
     * Update machine location
     */
    public function updateLocation($latitude, $longitude)
    {
        $this->update([
            'last_location_latitude' => $latitude,
            'last_location_longitude' => $longitude,
            'last_location_update' => now(),
        ]);
    }

    /**
     * Get latest metric
     */
    public function getLatestMetric()
    {
        return $this->metrics()->latest('created_at')->first();
    }
}

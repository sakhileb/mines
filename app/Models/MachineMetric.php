<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MachineMetric Model
 * 
 * Stores real-time metrics from machines
 * Includes engine data, fuel, temperature, and other sensor readings
 */
class MachineMetric extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'machine_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'altitude',
        'engine_rpm',
        'engine_temperature',
        'coolant_temperature',
        'oil_pressure',
        'fuel_level',
        'fuel_consumption_rate',
        'throttle_position',
        'battery_voltage',
        'total_hours',
        'idle_hours',
        'operating_hours',
        'load_weight',
        'payload_capacity_used',
        'tire_pressure_front_left',
        'tire_pressure_front_right',
        'tire_pressure_rear_left',
        'tire_pressure_rear_right',
        'raw_data', // JSON for any additional manufacturer-specific data
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'altitude' => 'float',
        'engine_rpm' => 'float',
        'engine_temperature' => 'float',
        'coolant_temperature' => 'float',
        'oil_pressure' => 'float',
        'fuel_level' => 'float',
        'fuel_consumption_rate' => 'float',
        'throttle_position' => 'float',
        'battery_voltage' => 'float',
        'total_hours' => 'float',
        'idle_hours' => 'float',
        'load_weight' => 'float',
        'payload_capacity_used' => 'float',
        'tire_pressure_front_left' => 'float',
        'tire_pressure_front_right' => 'float',
        'tire_pressure_rear_left' => 'float',
        'tire_pressure_rear_right' => 'float',
        'operating_hours' => 'float',
        'raw_data' => 'json',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the machine this metric belongs to
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the team this metric belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelTank extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'mine_area_id',
        'name',
        'tank_number',
        'location_description',
        'location_latitude',
        'location_longitude',
        'capacity_liters',
        'current_level_liters',
        'minimum_level_liters',
        'fuel_type',
        'status',
        'last_inspection_date',
        'next_inspection_date',
        'notes',
    ];

    protected $casts = [
        'capacity_liters' => 'decimal:2',
        'current_level_liters' => 'decimal:2',
        'minimum_level_liters' => 'decimal:2',
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(FuelAlert::class);
    }

    /**
     * Get the fill percentage
     */
    public function getFillPercentageAttribute(): float
    {
        if ($this->capacity_liters == 0) {
            return 0;
        }
        return round(($this->current_level_liters / $this->capacity_liters) * 100, 2);
    }

    /**
     * Check if tank is below minimum level
     */
    public function isBelowMinimum(): bool
    {
        return $this->current_level_liters < $this->minimum_level_liters;
    }

    /**
     * Check if tank is critical (below 10%)
     */
    public function isCritical(): bool
    {
        return $this->fill_percentage < 10;
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacityAttribute(): float
    {
        return $this->capacity_liters - $this->current_level_liters;
    }

    /**
     * Scope for active tanks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for low fuel tanks
     */
    public function scopeLowFuel($query)
    {
        return $query->whereRaw('current_level_liters < minimum_level_liters');
    }

    /**
     * Scope for critical fuel tanks
     */
    public function scopeCritical($query)
    {
        return $query->whereRaw('(current_level_liters / capacity_liters) < 0.1');
    }
}

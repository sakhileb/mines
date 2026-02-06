<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MineAreaProduction Model
 * 
 * Tracks production metrics for a mine area.
 * Records daily production, tonnage extracted, material types, etc.
 */
class MineAreaProduction extends Model
{
    use HasFactory;

    protected $table = 'mine_area_production';

    protected $fillable = [
        'mine_area_id',
        'recorded_date',
        'material_type',
        'tonnage',
        'volume_cubic_m',
        'machines_used',
        'operator_notes',
        'status',
        'metadata',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'tonnage' => 'float',
        'volume_cubic_m' => 'float',
        'machines_used' => 'json',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the mine area this production record belongs to.
     */
    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    /**
     * Scope to a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_date', [$startDate, $endDate]);
    }

    /**
     * Scope to a specific material type.
     */
    public function scopeMaterialType($query, string $type)
    {
        return $query->where('material_type', $type);
    }
}

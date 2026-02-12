<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'mine_area_id',
        'machine_id',
        'record_date',
        'shift',
        'quantity_produced',
        'unit',
        'target_quantity',
        'notes',
        'status',
        'metadata',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'target_quantity' => 'decimal:2',
        'record_date' => 'date',
        'metadata' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    public function scopeForMineArea($query, $mineAreaId)
    {
        return $query->where('mine_area_id', $mineAreaId);
    }

    public function getVariancePercentageAttribute(): float
    {
        if (!$this->target_quantity || $this->target_quantity == 0) {
            return 0;
        }
        return (($this->quantity_produced - $this->target_quantity) / $this->target_quantity) * 100;
    }

    public function getIsAboveTargetAttribute(): bool
    {
        if (!$this->target_quantity) {
            return false;
        }
        return $this->quantity_produced >= $this->target_quantity;
    }
}

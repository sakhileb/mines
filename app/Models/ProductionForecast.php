<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionForecast extends Model
{
    protected $fillable = [
        'team_id',
        'mine_area_id',
        'forecast_date',
        'forecasted_quantity',
        'unit',
        'confidence_level',
        'forecast_method',
    ];

    protected $casts = [
        'forecasted_quantity' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'forecast_date' => 'date',
        'forecast_method' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('forecast_date', $date);
    }

    public function scopeHighConfidence($query, $threshold = 80)
    {
        return $query->where('confidence_level', '>=', $threshold);
    }
}

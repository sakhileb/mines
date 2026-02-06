<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionForecast extends Model
{
    protected $fillable = [
        'mine_area_id',
        'forecast_date',
        'material_name',
        'predicted_tonnage',
        'confidence_score',
        'model_version',
        'factors',
    ];

    protected $casts = [
        'predicted_tonnage' => 'float',
        'confidence_score' => 'float',
        'factors' => 'json',
        'forecast_date' => 'date',
    ];

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    public function getReliabilityLevel(): string
    {
        if ($this->confidence_score >= 0.85) {
            return 'High';
        } elseif ($this->confidence_score >= 0.70) {
            return 'Medium';
        }
        return 'Low';
    }
}

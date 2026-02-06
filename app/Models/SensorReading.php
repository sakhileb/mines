<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'iot_sensor_id',
        'sensor_type',
        'value',
        'unit',
        'timestamp',
        'quality_score',
    ];

    protected $casts = [
        'value' => 'float',
        'quality_score' => 'float',
        'timestamp' => 'datetime',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(IoTSensor::class);
    }
}

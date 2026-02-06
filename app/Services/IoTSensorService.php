<?php

namespace App\Services;

use App\Models\MineArea;
use App\Models\IoTSensor;
use App\Models\SensorReading;
use Illuminate\Support\Collection;

class IoTSensorService
{
    /**
     * Register new IoT sensor
     */
    public function registerSensor(MineArea $mineArea, array $data): IoTSensor
    {
        return IoTSensor::create([
            'mine_area_id' => $mineArea->id,
            'name' => $data['name'],
            'sensor_type' => $data['sensor_type'],
            'device_id' => $data['device_id'],
            'status' => $data['status'] ?? 'active',
            'location_latitude' => $data['location_latitude'] ?? null,
            'location_longitude' => $data['location_longitude'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Record sensor reading
     */
    public function recordReading(IoTSensor $sensor, array $data): SensorReading
    {
        $reading = SensorReading::create([
            'iot_sensor_id' => $sensor->id,
            'sensor_type' => $sensor->sensor_type,
            'value' => $data['value'],
            'unit' => $data['unit'],
            'timestamp' => $data['timestamp'] ?? now(),
            'quality_score' => $data['quality_score'] ?? 1.0,
        ]);

        // Update sensor's last reading
        $sensor->update([
            'last_reading' => ['value' => $data['value'], 'unit' => $data['unit']],
            'last_reading_at' => now(),
        ]);

        return $reading;
    }

    /**
     * Get sensor readings with statistics
     */
    public function getReadingStats(IoTSensor $sensor, $days = 7): array
    {
        $readings = $sensor->readings()
            ->whereDate('timestamp', '>=', now()->subDays($days)->startOfDay())
            ->orderBy('timestamp')
            ->get();

        if ($readings->isEmpty()) {
            return [
                'count' => 0,
                'average' => null,
                'min' => null,
                'max' => null,
                'trend' => 'no_data',
            ];
        }

        $values = $readings->pluck('value')->toArray();
        
        return [
            'count' => count($values),
            'average' => array_sum($values) / count($values),
            'min' => min($values),
            'max' => max($values),
            'latest' => $readings->last()->value,
            'unit' => $readings->first()->unit,
            'trend' => $this->calculateTrend($values),
            'readings' => $readings->map(fn($r) => [
                'value' => $r->value,
                'timestamp' => $r->timestamp,
                'quality' => $r->quality_score,
            ])->toArray(),
        ];
    }

    /**
     * Get all active sensors for area
     */
    public function getActiveSensors(MineArea $mineArea): Collection
    {
        return $mineArea->sensors()
            ->where('status', 'active')
            ->orderBy('sensor_type')
            ->get();
    }

    /**
     * Check sensor health
     */
    public function checkSensorHealth(IoTSensor $sensor): array
    {
        $isOnline = $sensor->isOnline();
        $latestReading = $sensor->readings()->latest()->first();
        
        $lastReadingAge = $latestReading?->timestamp 
            ? now()->diffInMinutes($latestReading->timestamp)
            : null;

        return [
            'status' => $sensor->status,
            'is_online' => $isOnline,
            'last_reading_age_minutes' => $lastReadingAge,
            'health' => $isOnline ? 'healthy' : 'offline',
            'last_value' => $latestReading?->value,
            'last_reading_at' => $latestReading?->timestamp,
        ];
    }

    /**
     * Calculate trend from values
     */
    private function calculateTrend(array $values): string
    {
        if (count($values) < 2) {
            return 'insufficient_data';
        }

        $firstHalf = array_slice($values, 0, (int)(count($values) / 2));
        $secondHalf = array_slice($values, (int)(count($values) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $change = (($secondAvg - $firstAvg) / $firstAvg) * 100;

        if ($change > 5) {
            return 'increasing';
        } elseif ($change < -5) {
            return 'decreasing';
        }
        return 'stable';
    }

    /**
     * Bulk register sensors
     */
    public function bulkRegister(MineArea $mineArea, array $sensors): Collection
    {
        $registered = collect();

        foreach ($sensors as $sensorData) {
            $registered->push($this->registerSensor($mineArea, $sensorData));
        }

        return $registered;
    }

    /**
     * Deactivate sensor
     */
    public function deactivateSensor(IoTSensor $sensor): bool
    {
        return $sensor->update(['status' => 'inactive']);
    }
}

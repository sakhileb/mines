<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MineArea;
use App\Models\IoTSensor;
use App\Models\SensorReading;
use App\Services\IoTSensorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IoTSensorController extends Controller
{
    protected IoTSensorService $service;

    public function __construct(IoTSensorService $service)
    {
        $this->service = $service;
    }

    /**
     * Register new sensor
     */
    public function store(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sensor_type' => 'required|in:temperature,humidity,dust,vibration,noise,air_quality,pressure,custom',
            'device_id' => 'required|string|unique:iot_sensors',
            'location_latitude' => 'nullable|numeric|between:-90,90',
            'location_longitude' => 'nullable|numeric|between:-180,180',
            'metadata' => 'nullable|json',
        ]);

        $sensor = $this->service->registerSensor($mineArea, $validated);

        return response()->json($sensor, Response::HTTP_CREATED);
    }

    /**
     * Get all sensors for area
     */
    public function index(Request $request, MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $sensors = $mineArea->sensors()
            ->when($request->has('type'), fn($q) => $q->where('sensor_type', $request->type))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->status))
            ->paginate($request->get('per_page', 15));

        return response()->json($sensors);
    }

    /**
     * Get sensor details with health check
     */
    public function show(IoTSensor $sensor)
    {
        $health = $this->service->checkSensorHealth($sensor);
        $stats = $this->service->getReadingStats($sensor, 7);

        return response()->json([
            'sensor' => $sensor,
            'health' => $health,
            'statistics' => $stats,
        ]);
    }

    /**
     * Record sensor reading
     */
    public function recordReading(Request $request, IoTSensor $sensor)
    {
        $validated = $request->validate([
            'value' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'timestamp' => 'nullable|date_format:Y-m-d H:i:s',
            'quality_score' => 'nullable|numeric|between:0,1',
        ]);

        $reading = $this->service->recordReading($sensor, $validated);

        return response()->json($reading, Response::HTTP_CREATED);
    }

    /**
     * Get readings for sensor
     */
    public function readings(Request $request, IoTSensor $sensor)
    {
        $days = $request->get('days', 7);
        $type = $request->get('type', 'all');

        $query = $sensor->readings()
            ->whereDate('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp', 'desc');

        if ($type !== 'all') {
            $query->where('sensor_type', $type);
        }

        $readings = $query->paginate($request->get('per_page', 50));

        return response()->json($readings);
    }

    /**
     * Get reading statistics
     */
    public function statistics(Request $request, IoTSensor $sensor)
    {
        $days = $request->get('days', 7);
        $stats = $this->service->getReadingStats($sensor, $days);

        return response()->json($stats);
    }

    /**
     * Deactivate sensor
     */
    public function deactivate(IoTSensor $sensor)
    {
        $this->service->deactivateSensor($sensor);

        return response()->json(['message' => 'Sensor deactivated']);
    }

    /**
     * Bulk record readings
     */
    public function bulkReadings(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'readings' => 'required|array',
            'readings.*.sensor_id' => 'required|exists:iot_sensors,id',
            'readings.*.value' => 'required|numeric',
            'readings.*.unit' => 'required|string',
            'readings.*.timestamp' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $recorded = [];
        foreach ($validated['readings'] as $readingData) {
            $sensor = IoTSensor::find($readingData['sensor_id']);
            $recorded[] = $this->service->recordReading($sensor, $readingData);
        }

        return response()->json([
            'message' => sprintf('%d readings recorded', count($recorded)),
            'recorded' => $recorded,
        ], Response::HTTP_CREATED);
    }

    /**
     * Export sensor data
     */
    public function export(Request $request, IoTSensor $sensor)
    {
        $days = $request->get('days', 30);
        $format = $request->get('format', 'csv');

        $readings = $sensor->readings()
            ->whereDate('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp')
            ->get();

        if ($format === 'csv') {
            $csv = "Timestamp,Value,Unit,Quality Score\n";
            foreach ($readings as $reading) {
                $csv .= sprintf("%s,%s,%s,%s\n",
                    $reading->timestamp->format('Y-m-d H:i:s'),
                    $reading->value,
                    $reading->unit,
                    $reading->quality_score
                );
            }

            return response($csv, Response::HTTP_OK, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"sensor-{$sensor->id}.csv\"",
            ]);
        }

        return response()->json($readings);
    }
}

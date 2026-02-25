<?php

namespace App\Services\Integration;

use Exception;

/**
 * Volvo CareTrack Integration Service
 * 
 * Handles integration with Volvo CareTrack API (api.volvoce.com)
 * Uses OAuth 2.0 Client Credentials for authentication
 * Documentation: https://developer.volvoce.com/caretrack-api
 */
class VolvoService extends BaseManufacturerService
{
    /**
     * Manufacturer identifier
     */
    protected string $manufacturer = 'volvo';

    /**
     * Test connection to Volvo CareTrack API
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            // Test with machines endpoint
            $response = $this->makeRequest('GET', '/connected-machines/v1/machines');
            return !empty($response) && $response['success'] !== false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Fetch machines from Volvo CareTrack API
     * 
     * @return array
     */
    public function fetchMachines(): array
    {
        try {
            $response = $this->makeRequest('GET', '/connected-machines/v1/machines');
            
            $machines = [];
            if (!empty($response['data'])) {
                foreach ($response['data'] as $machine) {
                    $machines[] = $this->parseMachineData($machine);
                }
            }
            
            return [
                'success' => true,
                'machines' => $machines,
                'count' => count($machines),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch machines', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'machines' => [],
            ];
        }
    }

    /**
     * Fetch location data for equipment
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchLocation(string $machineId): array
    {
        try {
            $response = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/location");
            
            return [
                'success' => true,
                'location' => $this->parseLocation($response['data'] ?? []),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch location', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch telemetry/metrics for equipment
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchMetrics(string $machineId): array
    {
        try {
            // Fetch multiple metric types
            $telemetry = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/telemetry");
            $health = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/health");
            $utilization = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/utilization");
            $fuel = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/fuel");
            
            $metrics = [];
            
            // Parse telemetry data
            if (!empty($telemetry['data'])) {
                foreach ($telemetry['data'] as $metric) {
                    $metrics[] = $this->parseMetric($metric);
                }
            }
            
            // Add health metrics
            if (!empty($health['data'])) {
                $metrics[] = [
                    'type' => 'health_status',
                    'value' => $health['data']['status'] ?? 'unknown',
                    'timestamp' => $health['data']['timestamp'] ?? now(),
                ];
            }
            
            // Add utilization metrics
            if (!empty($utilization['data'])) {
                $metrics[] = [
                    'type' => 'utilization',
                    'value' => $utilization['data']['percentage'] ?? 0,
                    'timestamp' => $utilization['data']['timestamp'] ?? now(),
                ];
            }
            
            // Add fuel metrics
            if (!empty($fuel['data'])) {
                $metrics[] = [
                    'type' => 'fuel_level',
                    'value' => $fuel['data']['level'] ?? 0,
                    'unit' => $fuel['data']['unit'] ?? 'liters',
                    'timestamp' => $fuel['data']['timestamp'] ?? now(),
                ];
            }
            
            return [
                'success' => true,
                'metrics' => $metrics,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch metrics', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'metrics' => [],
            ];
        }
    }

    /**
     * Fetch alerts/faults for equipment
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchAlerts(string $machineId): array
    {
        try {
            // CareTrack includes alerts in health endpoint
            $response = $this->makeRequest('GET', "/connected-machines/v1/machines/{$machineId}/health");
            
            $alerts = [];
            if (!empty($response['data']['alerts'])) {
                foreach ($response['data']['alerts'] as $alert) {
                    $alerts[] = $this->parseAlert($alert);
                }
            }
            
            // Also check for faults/warnings
            if (!empty($response['data']['faults'])) {
                foreach ($response['data']['faults'] as $fault) {
                    $alerts[] = $this->parseAlert($fault);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch alerts', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'alerts' => [],
            ];
        }
    }

    /**
     * Parse equipment data from Volvo format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMachineData(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? $data['equipment_id'] ?? null,
            'name' => $data['name'] ?? $data['equipment_name'] ?? 'Unknown Equipment',
            'model' => $data['model'] ?? $data['model_name'] ?? 'Unknown Model',
            'manufacturer' => 'Volvo',
            'status' => $this->parseStatus($data['status'] ?? 'unknown'),
            'location' => $this->parseLocation($data['position'] ?? []),
            'last_heartbeat' => $data['last_update'] ?? $data['last_heartbeat'] ?? null,
            'specifications' => [
                'type' => $data['type'] ?? 'heavy_equipment',
                'model_code' => $data['model_code'] ?? null,
                'serial_number' => $data['serial_number'] ?? $data['serialNumber'] ?? null,
                'year_manufactured' => $data['year'] ?? $data['manufacture_year'] ?? null,
                'engine_power' => $data['engine_power'] ?? null,
                'weight' => $data['weight'] ?? null,
            ],
        ];
    }

    /**
     * Parse location data from Volvo format
     * 
     * @param array $data
     * @return array
     */
    protected function parseLocation(array $data): array
    {
        return [
            'latitude' => $data['latitude'] ?? $data['lat'] ?? 0,
            'longitude' => $data['longitude'] ?? $data['lng'] ?? 0,
            'altitude' => $data['altitude'] ?? $data['elevation'] ?? 0,
            'accuracy' => $data['gps_accuracy'] ?? $data['accuracy'] ?? 0,
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
        ];
    }

    /**
     * Parse diagnostic/metric data from Volvo format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMetric(array $data): array
    {
        return [
            'type' => $data['parameter_name'] ?? $data['type'] ?? 'unknown',
            'value' => $data['value'] ?? $data['reading'] ?? 0,
            'unit' => $data['unit'] ?? $data['measurement_unit'] ?? '',
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
            'tags' => [
                'parameter_id' => $data['parameter_id'] ?? null,
                'component' => $data['component'] ?? null,
            ],
        ];
    }

    /**
     * Parse alert/fault data from Volvo format
     * 
     * @param array $data
     * @return array
     */
    protected function parseAlert(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? $data['fault_id'] ?? null,
            'type' => $this->mapAlertType($data['fault_code'] ?? $data['type'] ?? 'unknown'),
            'priority' => $this->mapAlertPriority($data['priority'] ?? $data['severity'] ?? 'medium'),
            'message' => $data['description'] ?? $data['message'] ?? 'Fault detected',
            'timestamp' => $data['timestamp'] ?? $data['fault_time'] ?? now()->toIso8601String(),
            'acknowledged' => $data['acknowledged'] ?? false,
            'raw_data' => $data,
        ];
    }

    /**
     * Map Volvo equipment status to standard status
     * 
     * @param string $status
     * @return string
     */
    protected function parseStatus(string $status): string
    {
        $statusMap = [
            'online' => 'active',
            'offline' => 'inactive',
            'idle' => 'idle',
            'operating' => 'active',
            'in_operation' => 'active',
            'working' => 'active',
            'maintenance' => 'maintenance',
            'in_maintenance' => 'maintenance',
            'disabled' => 'stopped',
            'fault' => 'error',
            'error' => 'error',
        ];

        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}

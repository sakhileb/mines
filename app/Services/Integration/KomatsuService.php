<?php

namespace App\Services\Integration;

use Exception;

/**
 * Komatsu KOMTRAX Integration Service
 * 
 * Handles integration with Komatsu KOMTRAX API
 * Requires customer ID and Komatsu representative approval
 * Contact: Komatsu representative for API access
 */
class KomatsuService extends BaseManufacturerService
{
    /**
     * Manufacturer identifier
     */
    protected string $manufacturer = 'komatsu';

    /**
     * Test connection to Komatsu KOMTRAX API
     * 
     * @return array
     */
    public function testConnection(): array
    {
        try {
            // Test with machines endpoint
            $response = $this->makeRequest('GET', '/api/v2/machines');
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Komatsu KOMTRAX API',
                'status' => 'connected',
                'api_system' => 'KOMTRAX',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'CONNECTION_FAILED',
                'note' => 'Contact Komatsu representative for API access',
            ];
        }
    }

    /**
     * Fetch machines from Komatsu KOMTRAX API
     * 
     * @return array
     */
    public function fetchMachines(): array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v2/machines');
            
            $machines = [];
            if (!empty($response['machines'])) {
                foreach ($response['machines'] as $machine) {
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
            $response = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/location");
            
            return [
                'success' => true,
                'location' => $this->parseLocation($response ?? []),
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
     * Fetch performance/metrics for equipment
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchMetrics(string $machineId): array
    {
        try {
            // Fetch multiple metric types from KOMTRAX
            $operatingHours = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/operating-hours");
            $fuelConsumption = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/fuel-consumption");
            $workingModes = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/working-modes");
            $status = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/status");
            
            $metrics = [];
            
            // Add operating hours
            if (!empty($operatingHours['operatingHours'])) {
                $metrics[] = [
                    'type' => 'operating_hours',
                    'value' => $operatingHours['operatingHours']['total'] ?? 0,
                    'unit' => 'hours',
                    'timestamp' => $operatingHours['timestamp'] ?? now(),
                ];
            }
            
            // Add fuel consumption
            if (!empty($fuelConsumption['fuelConsumption'])) {
                $metrics[] = [
                    'type' => 'fuel_consumption',
                    'value' => $fuelConsumption['fuelConsumption']['total'] ?? 0,
                    'unit' => 'liters',
                    'timestamp' => $fuelConsumption['timestamp'] ?? now(),
                ];
            }
            
            // Add working modes
            if (!empty($workingModes['workingModes'])) {
                $metrics[] = [
                    'type' => 'working_mode',
                    'value' => $workingModes['workingModes']['current'] ?? 'unknown',
                    'timestamp' => $workingModes['timestamp'] ?? now(),
                ];
            }
            
            // Add machine status
            if (!empty($status['status'])) {
                $metrics[] = [
                    'type' => 'machine_status',
                    'value' => $status['status'],
                    'timestamp' => $status['timestamp'] ?? now(),
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
     * Fetch alerts/cautions for equipment
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchAlerts(string $machineId): array
    {
        try {
            // KOMTRAX uses 'cautions' instead of 'alerts'
            $response = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/cautions");
            
            $alerts = [];
            if (!empty($response['cautions'])) {
                foreach ($response['cautions'] as $caution) {
                    $alerts[] = $this->parseAlert($caution);
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
     * Parse equipment data from Komatsu format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMachineData(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? $data['asset_id'] ?? null,
            'name' => $data['name'] ?? $data['asset_name'] ?? 'Unknown Equipment',
            'model' => $data['model'] ?? $data['model_name'] ?? 'Unknown Model',
            'manufacturer' => 'Komatsu',
            'status' => $this->parseStatus($data['status'] ?? 'unknown'),
            'location' => $this->parseLocation($data['position'] ?? []),
            'last_heartbeat' => $data['last_heartbeat'] ?? $data['last_update'] ?? null,
            'specifications' => [
                'type' => $data['type'] ?? 'heavy_equipment',
                'model_code' => $data['model_code'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'year_manufactured' => $data['year_manufactured'] ?? null,
                'engine_model' => $data['engine_model'] ?? null,
                'operation_hours' => $data['operation_hours'] ?? null,
            ],
        ];
    }

    /**
     * Parse location data from Komatsu format
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
     * Parse performance/metric data from Komatsu format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMetric(array $data): array
    {
        return [
            'type' => $data['metric'] ?? $data['parameter'] ?? 'unknown',
            'value' => $data['value'] ?? $data['reading'] ?? 0,
            'unit' => $data['unit'] ?? '',
            'timestamp' => $data['recorded_at'] ?? $data['timestamp'] ?? now()->toIso8601String(),
            'tags' => [
                'category' => $data['category'] ?? null,
                'source' => $data['source'] ?? null,
            ],
        ];
    }

    /**
     * Parse alert/notification data from Komatsu format
     * 
     * @param array $data
     * @return array
     */
    protected function parseAlert(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? $data['notification_id'] ?? null,
            'type' => $this->mapAlertType($data['type'] ?? $data['category'] ?? 'unknown'),
            'priority' => $this->mapAlertPriority($data['level'] ?? $data['priority'] ?? 'medium'),
            'message' => $data['message'] ?? $data['description'] ?? 'Notification from Komatsu',
            'timestamp' => $data['timestamp'] ?? $data['created_at'] ?? now()->toIso8601String(),
            'acknowledged' => $data['acknowledged'] ?? false,
            'raw_data' => $data,
        ];
    }

    /**
     * Map Komatsu status to standard status
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
            'running' => 'active',
            'operating' => 'active',
            'maintenance' => 'maintenance',
            'standby' => 'idle',
            'error' => 'error',
            'unavailable' => 'stopped',
        ];

        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}

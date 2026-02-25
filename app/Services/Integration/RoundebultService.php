<?php

namespace App\Services\Integration;

use Exception;

/**
 * Roundebult Fleet Management Integration Service
 * 
 * Contact Roundebult for API access
 * South African fleet management provider
 */
class RoundebultService extends BaseManufacturerService
{
    /**
     * Manufacturer identifier
     */
    protected string $manufacturer = 'roundebult';

    /**
     * Test connection to Roundebult API
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/api/v1/machines', [
                'query' => ['limit' => 1]
            ]);
            return !empty($response) && $response['success'] !== false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Fetch machines from Roundebult API
     * 
     * @return array
     */
    public function fetchMachines(): array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v1/machines');
            
            $machines = [];
            if (!empty($response['data']['machines'])) {
                foreach ($response['data']['machines'] as $machine) {
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
     * Fetch location data for a machine
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchLocation(string $machineId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/location");
            
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
     * Fetch metrics for a machine
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchMetrics(string $machineId): array
    {
        try {
            $metrics = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/metrics");
            $operations = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/operations");
            
            $allMetrics = array_merge(
                $this->parseMetrics($metrics['data'] ?? []),
                $this->parseMetrics($operations['data'] ?? [])
            );
            
            return [
                'success' => true,
                'metrics' => $allMetrics,
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
     * Fetch alerts for a machine
     * 
     * @param string $machineId
     * @return array
     */
    public function fetchAlerts(string $machineId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/alerts");
            
            $alerts = [];
            if (!empty($response['data']['alerts'])) {
                foreach ($response['data']['alerts'] as $alert) {
                    $alerts[] = $this->parseAlert($alert);
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
     * Parse machine data from Roundebult format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMachineData(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? null,
            'name' => $data['name'] ?? 'Unknown Machine',
            'model' => $data['model'] ?? 'Unknown Model',
            'manufacturer' => 'Roundebult',
            'status' => $this->parseStatus($data['status'] ?? 'unknown'),
            'location' => $this->parseLocation($data['location'] ?? []),
            'last_heartbeat' => $data['last_heartbeat'] ?? null,
            'specifications' => [
                'type' => $data['type'] ?? 'mining_machine',
                'capacity' => $data['capacity'] ?? null,
                'year_manufactured' => $data['year_manufactured'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
            ],
        ];
    }

    /**
     * Parse location data from Roundebult format
     * 
     * @param array $data
     * @return array
     */
    protected function parseLocation(array $data): array
    {
        return [
            'latitude' => $data['lat'] ?? $data['latitude'] ?? 0,
            'longitude' => $data['lng'] ?? $data['longitude'] ?? 0,
            'altitude' => $data['altitude'] ?? 0,
            'accuracy' => $data['accuracy'] ?? 0,
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
        ];
    }

    /**
     * Parse metric data from Roundebult format
     * 
     * @param array $data
     * @return array
     */
    protected function parseMetric(array $data): array
    {
        return [
            'type' => $data['type'] ?? 'unknown',
            'value' => $data['value'] ?? 0,
            'unit' => $data['unit'] ?? '',
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
            'tags' => $data['tags'] ?? [],
        ];
    }

    /**
     * Parse alert data from Roundebult format
     * 
     * @param array $data
     * @return array
     */
    protected function parseAlert(array $data): array
    {
        return [
            'external_id' => $data['id'] ?? null,
            'type' => $this->mapAlertType($data['alert_type'] ?? 'unknown'),
            'priority' => $this->mapAlertPriority($data['severity'] ?? 'medium'),
            'message' => $data['message'] ?? 'Alert from Roundebult',
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
            'acknowledged' => $data['acknowledged'] ?? false,
            'raw_data' => $data,
        ];
    }

    /**
     * Map Roundebult status to standard status
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
            'working' => 'active',
            'maintenance' => 'maintenance',
            'stopped' => 'stopped',
            'error' => 'error',
        ];

        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}

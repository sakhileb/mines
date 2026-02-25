<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Kubota Diagnostics Integration Service
 * 
 * Contact Kubota dealer for API access
 * Requires Kubota dealer ID
 */
class KubotaService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'kubota';

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

    public function fetchMetrics(string $machineId): array
    {
        try {
            $telemetry = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/telemetry");
            $diagnostics = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/diagnostics");
            $service = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/service-history");
            
            $metrics = array_merge(
                $this->parseMetrics($telemetry['data'] ?? []),
                $this->parseMetrics($diagnostics['data'] ?? []),
                $this->parseMetrics($service['data'] ?? [])
            );
            
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

    public function fetchAlerts(string $machineId): array
    {
        try {
            // Kubota uses diagnostics endpoint for alerts
            $response = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/diagnostics");
            
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
            $this->logError('Failed to fetch diagnostics', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'alerts' => [],
            ];
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}

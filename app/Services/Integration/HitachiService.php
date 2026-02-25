<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Hitachi Construction Machinery ConSite Integration Service
 * 
 * API Documentation: https://www.consite.com/api-docs
 * Requires customer code from Hitachi
 */
class HitachiService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'hitachi';

    /**
     * Test connection to Hitachi ConSite API
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/api/v2/machines', [
                'query' => ['limit' => 1]
            ]);
            return !empty($response) && $response['success'] !== false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Fetch machines from Hitachi ConSite
     */
    public function fetchMachines(): array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v2/machines');
            
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
     * Fetch machine location
     */
    public function fetchLocation(string $machineId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/location");
            
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
     * Fetch machine metrics
     */
    public function fetchMetrics(string $machineId): array
    {
        try {
            // Fetch multiple metric endpoints
            $operatingHours = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/operating-hours");
            $status = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/status");
            $diagnostics = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/diagnostics");
            
            $metrics = array_merge(
                $this->parseMetrics($operatingHours['data'] ?? []),
                $this->parseMetrics($status['data'] ?? []),
                $this->parseMetrics($diagnostics['data'] ?? [])
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

    /**
     * Fetch machine alerts
     */
    public function fetchAlerts(string $machineId): array
    {
        try {
            $response = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/alerts");
            
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

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}

<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Kobelco KIMS (Kobelco Information Management System) Integration Service
 * 
 * Contact Kobelco for KIMS API access
 * Requires Kobelco customer code
 */
class KobelcoService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'kobelco';

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
            $operatingStatus = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/operating-status");
            $workRecords = $this->makeRequest('GET', "/api/v1/machines/{$machineId}/work-records");
            
            $metrics = array_merge(
                $this->parseMetrics($operatingStatus['data'] ?? []),
                $this->parseMetrics($workRecords['data'] ?? [])
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

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}

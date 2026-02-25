<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Doosan DoosanCONNECT Integration Service
 * 
 * API Documentation: https://developer.doosan.com/connect-api
 * Requires Doosan account ID
 */
class DoosanService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'doosan';

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

    public function fetchMetrics(string $machineId): array
    {
        try {
            $operation = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/operation");
            $fuel = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/fuel");
            $maintenance = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/maintenance");
            
            $metrics = array_merge(
                $this->parseMetrics($operation['data'] ?? []),
                $this->parseMetrics($fuel['data'] ?? []),
                $this->parseMetrics($maintenance['data'] ?? [])
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
            $response = $this->makeRequest('GET', "/api/v2/machines/{$machineId}/warnings");
            
            $alerts = [];
            if (!empty($response['data']['warnings'])) {
                foreach ($response['data']['warnings'] as $warning) {
                    $alerts[] = $this->parseAlert($warning);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch warnings', $e);
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

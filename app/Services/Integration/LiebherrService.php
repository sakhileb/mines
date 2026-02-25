<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Liebherr LiDAT Integration Service
 * 
 * API Documentation: https://www.liebherr.com/lidat-api
 * Requires Liebherr customer ID
 */
class LiebherrService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'liebherr';

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/api/v2/equipment', [
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
            $response = $this->makeRequest('GET', '/api/v2/equipment');
            
            $machines = [];
            if (!empty($response['data']['equipment'])) {
                foreach ($response['data']['equipment'] as $equipment) {
                    $machines[] = $this->parseMachineData($equipment);
                }
            }
            
            return [
                'success' => true,
                'machines' => $machines,
                'count' => count($machines),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch equipment', $e);
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
            $response = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/position");
            
            return [
                'success' => true,
                'location' => $this->parseLocation($response['data'] ?? []),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch position', $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function fetchMetrics(string $machineId): array
    {
        try {
            $operatingData = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/operating-data");
            $telemetry = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/telemetry");
            $serviceIntervals = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/service-intervals");
            
            $metrics = array_merge(
                $this->parseMetrics($operatingData['data'] ?? []),
                $this->parseMetrics($telemetry['data'] ?? []),
                $this->parseMetrics($serviceIntervals['data'] ?? [])
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
            $response = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/error-codes");
            
            $alerts = [];
            if (!empty($response['data']['errorCodes'])) {
                foreach ($response['data']['errorCodes'] as $error) {
                    $alerts[] = $this->parseAlert($error);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch error codes', $e);
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

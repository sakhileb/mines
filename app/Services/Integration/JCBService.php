<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * JCB LiveLink Integration Service
 * 
 * API Documentation: https://developer.jcb.com/livelink-api
 * Requires JCB dealer ID
 */
class JCBService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'jcb';

    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest('GET', '/livelink/v1/machines', [
                'query' => ['limit' => 1]
            ]);
            
            return [
                'success' => true,
                'message' => 'Successfully connected to JCB LiveLink API',
                'api_system' => 'LiveLink',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'CONNECTION_FAILED',
            ];
        }
    }

    public function fetchMachines(): array
    {
        try {
            $response = $this->makeRequest('GET', '/livelink/v1/machines');
            
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
            $response = $this->makeRequest('GET', "/livelink/v1/machines/{$machineId}/location");
            
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
            $telemetry = $this->makeRequest('GET', "/livelink/v1/machines/{$machineId}/telemetry");
            $utilization = $this->makeRequest('GET', "/livelink/v1/machines/{$machineId}/utilization");
            $service = $this->makeRequest('GET', "/livelink/v1/machines/{$machineId}/service");
            
            $metrics = array_merge(
                $this->parseMetrics($telemetry['data'] ?? []),
                $this->parseMetrics($utilization['data'] ?? []),
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
            $response = $this->makeRequest('GET', "/livelink/v1/machines/{$machineId}/alerts");
            
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

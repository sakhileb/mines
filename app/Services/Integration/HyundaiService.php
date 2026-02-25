<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Hyundai Hi-MATE Integration Service
 * 
 * API Documentation: https://www.hi-mate.com/api-documentation
 * Requires Hyundai dealer code
 */
class HyundaiService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'hyundai';

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/api/v1/equipment', [
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
            $response = $this->makeRequest('GET', '/api/v1/equipment');
            
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
            $response = $this->makeRequest('GET', "/api/v1/equipment/{$machineId}/location");
            
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
            $workingInfo = $this->makeRequest('GET', "/api/v1/equipment/{$machineId}/working-info");
            $engineData = $this->makeRequest('GET', "/api/v1/equipment/{$machineId}/engine-data");
            $serviceInfo = $this->makeRequest('GET', "/api/v1/equipment/{$machineId}/service-info");
            
            $metrics = array_merge(
                $this->parseMetrics($workingInfo['data'] ?? []),
                $this->parseMetrics($engineData['data'] ?? []),
                $this->parseMetrics($serviceInfo['data'] ?? [])
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
            $response = $this->makeRequest('GET', "/api/v1/equipment/{$machineId}/notifications");
            
            $alerts = [];
            if (!empty($response['data']['notifications'])) {
                foreach ($response['data']['notifications'] as $notification) {
                    $alerts[] = $this->parseAlert($notification);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch notifications', $e);
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

<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * XCMG Xrea (XCMG Remote Expert Assistant) Integration Service
 * 
 * Contact XCMG for API access
 * Requires XCMG company ID
 */
class XCMGService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'xcmg';

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/iot/v1/devices', [
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
            $response = $this->makeRequest('GET', '/iot/v1/devices');
            
            $machines = [];
            if (!empty($response['data']['devices'])) {
                foreach ($response['data']['devices'] as $device) {
                    $machines[] = $this->parseMachineData($device);
                }
            }
            
            return [
                'success' => true,
                'machines' => $machines,
                'count' => count($machines),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch devices', $e);
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
            $response = $this->makeRequest('GET', "/iot/v1/devices/{$machineId}/location");
            
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
            $status = $this->makeRequest('GET', "/iot/v1/devices/{$machineId}/status");
            $parameters = $this->makeRequest('GET', "/iot/v1/devices/{$machineId}/parameters");
            $workData = $this->makeRequest('GET', "/iot/v1/devices/{$machineId}/work-data");
            
            $metrics = array_merge(
                $this->parseMetrics($status['data'] ?? []),
                $this->parseMetrics($parameters['data'] ?? []),
                $this->parseMetrics($workData['data'] ?? [])
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
            $response = $this->makeRequest('GET', "/iot/v1/devices/{$machineId}/faults");
            
            $alerts = [];
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
            $this->logError('Failed to fetch faults', $e);
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

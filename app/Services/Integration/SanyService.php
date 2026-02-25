<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Sany SUMS (Sany Unique Management System) Integration Service
 * 
 * Contact Sany representative for API access
 * Requires Sany enterprise ID
 */
class SanyService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'sany';

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/open/v1/devices', [
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
            $response = $this->makeRequest('GET', '/open/v1/devices');
            
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
            $response = $this->makeRequest('GET', "/open/v1/devices/{$machineId}/location");
            
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
            $realtimeData = $this->makeRequest('GET', "/open/v1/devices/{$machineId}/realtime");
            $workingHours = $this->makeRequest('GET', "/open/v1/devices/{$machineId}/working-hours");
            $statistics = $this->makeRequest('GET', "/open/v1/devices/{$machineId}/statistics");
            
            $metrics = array_merge(
                $this->parseMetrics($realtimeData['data'] ?? []),
                $this->parseMetrics($workingHours['data'] ?? []),
                $this->parseMetrics($statistics['data'] ?? [])
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
            $response = $this->makeRequest('GET', "/open/v1/devices/{$machineId}/alarms");
            
            $alerts = [];
            if (!empty($response['data']['alarms'])) {
                foreach ($response['data']['alarms'] as $alarm) {
                    $alerts[] = $this->parseAlert($alarm);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch alarms', $e);
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

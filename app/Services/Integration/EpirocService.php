<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Exception;

/**
 * Epiroc Certiq Integration Service (formerly Atlas Copco)
 * 
 * API Documentation: https://certiq.com/api-documentation
 * Requires Epiroc customer ID and Certiq subscription
 */
class EpirocService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer = 'epiroc';

    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v2/equipment', [
                'query' => ['limit' => 1]
            ]);
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Epiroc Certiq API',
                'api_system' => 'Certiq',
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
            $response = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/location");
            
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
            $performance = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/performance");
            $production = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/production");
            $maintenance = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/maintenance");
            
            $metrics = array_merge(
                $this->parseMetrics($performance['data'] ?? []),
                $this->parseMetrics($production['data'] ?? []),
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
            $response = $this->makeRequest('GET', "/api/v2/equipment/{$machineId}/events");
            
            $alerts = [];
            if (!empty($response['data']['events'])) {
                foreach ($response['data']['events'] as $event) {
                    $alerts[] = $this->parseAlert($event);
                }
            }
            
            return [
                'success' => true,
                'alerts' => $alerts,
            ];
        } catch (Exception $e) {
            $this->logError('Failed to fetch events', $e);
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

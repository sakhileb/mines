<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseManufacturerService implements ManufacturerServiceInterface
{
    protected string $manufacturer;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected ?string $lastError = null;
    protected int $timeout = 30;
    protected int $retries = 3;
    protected int $retryDelay = 1000; // milliseconds

    /**
     * Initialize the service with API credentials
     *
     * @param array $credentials
     */
    public function __construct(array $credentials = [])
    {
        $this->baseUrl = $credentials['base_url'] ?? '';
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->apiSecret = $credentials['api_secret'] ?? '';
    }

    /**
     * Make HTTP request to manufacturer API with retry logic
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return array
     */
    protected function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getAuthHeaders($headers))
                    ->retry($attempt, $this->retryDelay)
                    ->{strtolower($method)}($url, $data);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'status' => $response->status(),
                    ];
                } else {
                    $this->lastError = "API returned status {$response->status()}: {$response->body()}";
                    Log::warning("Integration API Error: {$this->lastError}", [
                        'manufacturer' => $this->manufacturer,
                        'endpoint' => $endpoint,
                    ]);
                }
            } catch (\Exception $e) {
                $lastException = $e;
                $this->lastError = $e->getMessage();
                
                Log::warning("Integration API Exception: {$this->lastError}", [
                    'manufacturer' => $this->manufacturer,
                    'endpoint' => $endpoint,
                    'attempt' => $attempt + 1,
                ]);

                if ($attempt < $this->retries - 1) {
                    usleep($this->retryDelay * 1000);
                }
            }

            $attempt++;
        }

        return [
            'success' => false,
            'error' => $this->lastError ?? 'Unknown error occurred',
            'exception' => $lastException,
        ];
    }

    /**
     * Get authentication headers for API requests
     *
     * @param array $additionalHeaders
     * @return array
     */
    protected function getAuthHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ], $additionalHeaders);
    }

    /**
     * Parse machine data from API response to standard format
     *
     * @param array $rawData
     * @return array
     */
    protected function parseMachineData(array $rawData): array
    {
        return [
            'external_id' => $rawData['id'] ?? null,
            'manufacturer' => $this->manufacturer,
            'model' => $rawData['model'] ?? 'Unknown',
            'serial_number' => $rawData['serial_number'] ?? null,
            'status' => $this->parseStatus($rawData['status'] ?? 'unknown'),
            'last_location' => [
                'latitude' => $rawData['latitude'] ?? null,
                'longitude' => $rawData['longitude'] ?? null,
                'timestamp' => $rawData['location_timestamp'] ?? now(),
            ],
            'metrics' => $rawData['metrics'] ?? [],
            'alerts' => $rawData['alerts'] ?? [],
        ];
    }

    /**
     * Parse location data from API response
     *
     * @param array $rawData
     * @return array|null
     */
    protected function parseLocation(array $rawData): ?array
    {
        if (empty($rawData['latitude']) || empty($rawData['longitude'])) {
            return null;
        }

        return [
            'latitude' => (float) $rawData['latitude'],
            'longitude' => (float) $rawData['longitude'],
            'accuracy' => $rawData['accuracy'] ?? null,
            'timestamp' => $rawData['timestamp'] ?? now(),
            'heading' => $rawData['heading'] ?? null,
            'speed' => $rawData['speed'] ?? null,
        ];
    }

    /**
     * Parse metrics from API response
     *
     * @param array $rawData
     * @return array
     */
    protected function parseMetrics(array $rawData): array
    {
        return [
            'timestamp' => $rawData['timestamp'] ?? now(),
            'engine_rpm' => $rawData['engine_rpm'] ?? null,
            'engine_temp' => $rawData['engine_temp'] ?? null,
            'fuel_level' => $rawData['fuel_level'] ?? null,
            'fuel_consumption' => $rawData['fuel_consumption'] ?? null,
            'hydraulic_pressure' => $rawData['hydraulic_pressure'] ?? null,
            'oil_pressure' => $rawData['oil_pressure'] ?? null,
            'coolant_temp' => $rawData['coolant_temp'] ?? null,
            'battery_voltage' => $rawData['battery_voltage'] ?? null,
            'operating_hours' => $rawData['operating_hours'] ?? null,
            'load_weight' => $rawData['load_weight'] ?? null,
            'raw_data' => $rawData,
        ];
    }

    /**
     * Parse alerts from API response
     *
     * @param array $alerts
     * @return array
     */
    protected function parseAlerts(array $alerts): array
    {
        return array_map(function ($alert) {
            return [
                'external_id' => $alert['id'] ?? null,
                'title' => $alert['title'] ?? 'Alert',
                'description' => $alert['description'] ?? '',
                'type' => $this->mapAlertType($alert['type'] ?? 'sensor'),
                'priority' => $this->mapAlertPriority($alert['severity'] ?? 'medium'),
                'status' => $alert['status'] ?? 'new',
                'timestamp' => $alert['timestamp'] ?? now(),
            ];
        }, $alerts);
    }

    /**
     * Map machine status from manufacturer format to standard
     *
     * @param string $status
     * @return string
     */
    protected function parseStatus(string $status): string
    {
        $statusMap = [
            'active' => 'active',
            'running' => 'active',
            'in_use' => 'active',
            'idle' => 'idle',
            'parked' => 'idle',
            'offline' => 'offline',
            'maintenance' => 'maintenance',
            'service' => 'maintenance',
        ];

        return $statusMap[strtolower($status)] ?? 'unknown';
    }

    /**
     * Map alert type from manufacturer format to standard
     *
     * @param string $type
     * @return string
     */
    protected function mapAlertType(string $type): string
    {
        $typeMap = [
            'temperature' => 'temperature',
            'fuel' => 'fuel',
            'maintenance' => 'maintenance',
            'sensor' => 'sensor',
            'geofence' => 'geofence',
            'downtime' => 'downtime',
            'error' => 'sensor',
            'warning' => 'sensor',
        ];

        return $typeMap[strtolower($type)] ?? 'sensor';
    }

    /**
     * Map alert priority from manufacturer format to standard
     *
     * @param string $severity
     * @return string
     */
    protected function mapAlertPriority(string $severity): string
    {
        $priorityMap = [
            'critical' => 'critical',
            'error' => 'high',
            'warning' => 'medium',
            'info' => 'low',
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
        ];

        return $priorityMap[strtolower($severity)] ?? 'medium';
    }

    /**
     * Get manufacturer name
     *
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * Get last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Log integration activity
     *
     * @param string $action
     * @param array $details
     * @return void
     */
    protected function logActivity(string $action, array $details = []): void
    {
        Log::info("Integration Activity: {$action}", array_merge([
            'manufacturer' => $this->manufacturer,
            'timestamp' => now(),
        ], $details));
    }
}

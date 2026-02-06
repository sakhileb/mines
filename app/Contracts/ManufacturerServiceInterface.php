<?php

namespace App\Contracts;

interface ManufacturerServiceInterface
{
    /**
     * Test the connection to the manufacturer API
     *
     * @return bool
     */
    public function testConnection(): bool;

    /**
     * Fetch all machines from the manufacturer API
     *
     * @return array
     */
    public function fetchMachines(): array;

    /**
     * Fetch machine details from the manufacturer API
     *
     * @param string $machineId
     * @return array
     */
    public function fetchMachineDetails(string $machineId): array;

    /**
     * Fetch real-time location for a machine
     *
     * @param string $machineId
     * @return array|null
     */
    public function fetchMachineLocation(string $machineId): ?array;

    /**
     * Fetch machine metrics/diagnostics (fuel, temperature, RPM, etc.)
     *
     * @param string $machineId
     * @return array
     */
    public function fetchMachineMetrics(string $machineId): array;

    /**
     * Fetch machine alerts/faults
     *
     * @param string $machineId
     * @return array
     */
    public function fetchMachineAlerts(string $machineId): array;

    /**
     * Fetch all data for a machine (comprehensive sync)
     *
     * @param string $machineId
     * @return array
     */
    public function fetchMachineData(string $machineId): array;

    /**
     * Get the manufacturer name
     *
     * @return string
     */
    public function getManufacturer(): string;

    /**
     * Get API error if any occurred
     *
     * @return string|null
     */
    public function getLastError(): ?string;
}

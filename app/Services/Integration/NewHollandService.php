<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;

class NewHollandService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    public function testConnection(): bool
    {
        // Implement New Holland API connection test
        return true;
    }

    public function fetchMachines(): array
    {
        // Implement New Holland API fetch logic
        return [];
    }

    public function getLastError(): ?string
    {
        // Return last error if any
        return null;
    }
}

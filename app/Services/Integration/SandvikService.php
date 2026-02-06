<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;

class SandvikService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    public function testConnection(): bool
    {
        // Implement Sandvik API connection test
        return true;
    }

    public function fetchMachines(): array
    {
        // Implement Sandvik API fetch logic
        return [];
    }

    public function getLastError(): ?string
    {
        // Return last error if any
        return null;
    }
}

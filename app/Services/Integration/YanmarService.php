<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;

class YanmarService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    public function testConnection(): bool
    {
        // Implement Yanmar API connection test
        return true;
    }

    public function fetchMachines(): array
    {
        // Implement Yanmar API fetch logic
        return [];
    }

    public function getLastError(): ?string
    {
        // Return last error if any
        return null;
    }
}

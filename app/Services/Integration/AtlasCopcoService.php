<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;

class AtlasCopcoService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    public function testConnection(): bool
    {
        // Implement Atlas Copco API connection test
        return true;
    }

    public function fetchMachines(): array
    {
        // Implement Atlas Copco API fetch logic
        return [];
    }

    public function getLastError(): ?string
    {
        // Return last error if any
        return null;
    }
}

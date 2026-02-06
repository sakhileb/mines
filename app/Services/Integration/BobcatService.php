<?php

namespace App\Services\Integration;

use App\Contracts\ManufacturerServiceInterface;

class BobcatService extends BaseManufacturerService implements ManufacturerServiceInterface
{
    public function testConnection(): bool
    {
        // Implement Bobcat API connection test
        return true;
    }

    public function fetchMachines(): array
    {
        // Implement Bobcat API fetch logic
        return [];
    }

    public function getLastError(): ?string
    {
        // Return last error if any
        return null;
    }
}

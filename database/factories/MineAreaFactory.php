<?php

namespace Database\Factories;

use App\Models\MineArea;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MineArea>
 */
class MineAreaFactory extends Factory
{
    protected $model = MineArea::class;

    public function definition(): array
    {
        // Generate coordinates for a polygon around a point in South Africa mining region
        $centerLat = $this->faker->latitude(-30, -25);
        $centerLng = $this->faker->longitude(25, 30);
        
        // Create a polygon with 4 corners (rectangle)
        $offset = 0.01; // ~1km
        $coordinates = [
            [$centerLng - $offset, $centerLat - $offset],
            [$centerLng + $offset, $centerLat - $offset],
            [$centerLng + $offset, $centerLat + $offset],
            [$centerLng - $offset, $centerLat + $offset],
            [$centerLng - $offset, $centerLat - $offset], // Close the polygon
        ];

        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->words(2, true) . ' Mine Area',
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['pit', 'stockpile', 'processing', 'dump', 'facility']),
            'coordinates' => $coordinates,
            'center_latitude' => $centerLat,
            'center_longitude' => $centerLng,
            'area_sqm' => $this->faker->randomFloat(2, 10000, 500000), // 10k to 500k sqm
            'perimeter_m' => $this->faker->randomFloat(2, 500, 5000), // 500m to 5km
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the mine area is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the mine area is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Create a pit mine area.
     */
    public function pit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pit',
        ]);
    }

    /**
     * Create a stockpile mine area.
     */
    public function stockpile(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'stockpile',
        ]);
    }
}

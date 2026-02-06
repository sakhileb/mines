<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MineArea Model
 * 
 * Represents a mine area or pit defined by coordinates.
 * Can have multiple machines assigned to it.
 * Tracks production area, boundaries, and metadata.
 */
class MineArea extends Model
{
    use HasFactory, HasTeamFilters;

    protected $table = 'mine_areas';

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'type', // pit, stockpile, dump, processing, facility
        'coordinates', // JSON array of [lat, lon] pairs forming polygon
        'center_latitude',
        'center_longitude',
        'area_sqm', // calculated area in square meters
        'perimeter_m', // calculated perimeter in meters
        'status', // active, inactive, archived
        'notes',
        'metadata', // JSON for custom fields
        'shifts', // JSON array of shift configurations
        'material_types', // JSON array of material types allocated
        'mining_targets', // JSON object with daily/weekly/monthly/yearly targets
    ];

    protected $casts = [
        'coordinates' => 'json',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'area_sqm' => 'float',
        'perimeter_m' => 'float',
        'metadata' => 'json',
        'shifts' => 'json',
        'material_types' => 'json',
        'mining_targets' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the team that owns this mine area.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all machines assigned to this mine area.
     */
    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class, 'mine_area_machine')
            ->withPivot('assigned_at', 'notes')
            ->withTimestamps();
    }

    /**
     * Get all mine plans uploaded for this area.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(MinePlan::class);
    }

    /**
     * Get all production records for this mine area.
     */
    public function production(): HasMany
    {
        return $this->hasMany(MineAreaProduction::class);
    }

    /**
     * Scope to only active mine areas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to a specific type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate the area (in square meters) of the polygon.
     * Uses the Shoelace formula for polygon area calculation.
     */
    public static function calculateArea(array $coordinates): float
    {
        if (count($coordinates) < 3) {
            return 0;
        }

        $area = 0;
        $n = count($coordinates);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;

            // [latitude, longitude] order
            $lat1 = deg2rad($coordinates[$i][0]);
            $lon1 = deg2rad($coordinates[$i][1]);
            $lat2 = deg2rad($coordinates[$j][0]);
            $lon2 = deg2rad($coordinates[$j][1]);

            $area += sin($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        $area = abs($area * 6371009 * 6371009 / 2);

        return round($area, 2);
    }

    /**
     * Calculate the perimeter (in meters) of the polygon.
     */
    public static function calculatePerimeter(array $coordinates): float
    {
        if (count($coordinates) < 2) {
            return 0;
        }

        $perimeter = 0;
        $n = count($coordinates);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;

            // [latitude, longitude] order
            $perimeter += self::haversineDistance(
                $coordinates[$i][0], // lat1
                $coordinates[$i][1], // lon1
                $coordinates[$j][0], // lat2
                $coordinates[$j][1]  // lon2
            );
        }

        return round($perimeter, 2);
    }

    /**
     * Calculate center point of the polygon.
     */
    public static function calculateCenter(array $coordinates): array
    {
        $sumLat = 0;
        $sumLon = 0;

        foreach ($coordinates as $coord) {
            // [latitude, longitude] order
            $sumLat += $coord[0];
            $sumLon += $coord[1];
        }

        return [
            'latitude' => $sumLat / count($coordinates),
            'longitude' => $sumLon / count($coordinates),
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    private static function haversineDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadiusKm * $c;

        return $distance * 1000; // Convert to meters
    }

    /**
     * Check if a point is inside this mine area using ray casting.
     */
    public function containsPoint(float $lat, float $lon): bool
    {
        return self::isPointInPolygon($lat, $lon, $this->coordinates);
    }

    /**
     * Determine if a point is inside a polygon using ray casting algorithm.
     */
    public static function isPointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        if (empty($polygon) || count($polygon) < 3) {
            return false;
        }

        $inside = false;
        $p1Lat = $polygon[0][0];
        $p1Lon = $polygon[0][1];

        for ($i = 1; $i <= count($polygon); $i++) {
            $p2Lat = $polygon[$i % count($polygon)][0];
            $p2Lon = $polygon[$i % count($polygon)][1];

            if ($lat > min($p1Lat, $p2Lat)) {
                if ($lat <= max($p1Lat, $p2Lat)) {
                    if ($lon <= max($p1Lon, $p2Lon)) {
                        $xinters = ($lat - $p1Lat) * ($p2Lon - $p1Lon) / ($p2Lat - $p1Lat) + $p1Lon;
                        if ($p1Lon == $p2Lon || $lon <= $xinters) {
                            $inside = !$inside;
                        }
                    }
                }
            }

            $p1Lat = $p2Lat;
            $p1Lon = $p2Lon;
        }

        return $inside;
    }

    /**
     * Get active shifts for this mine area
     */
    public function getActiveShifts()
    {
        return $this->shifts ?? [];
    }

    /**
     * Check if a shift exists
     */
    public function hasShift($shiftName): bool
    {
        $shifts = $this->getActiveShifts();
        foreach ($shifts as $shift) {
            if (($shift['name'] ?? '') === $shiftName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get allocated material types
     */
    public function getMaterialTypes()
    {
        return $this->material_types ?? [];
    }

    /**
     * Check if material type is allocated
     */
    public function hasMaterialType($materialType): bool
    {
        $materials = $this->getMaterialTypes();
        return in_array($materialType, $materials);
    }

    /**
     * Get mining targets
     */
    public function getMiningTargets()
    {
        return $this->mining_targets ?? [
            'daily' => 0,
            'weekly' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'unit' => 'tonnes'
        ];
    }

    /**
     * Get target for specific period
     */
    public function getTarget($period): float
    {
        $targets = $this->getMiningTargets();
        return $targets[$period] ?? 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FleetMarketListing Model
 *
 * Represents a machine listed for sale on the Fleet Market by a mine team.
 *
 * @property int $id
 * @property int $team_id
 * @property string $brand
 * @property string $model
 * @property string $machine_type
 * @property int|null $year
 * @property float|null $price
 * @property string $currency
 * @property string $condition
 * @property int|null $hours_on_machine
 * @property string|null $description
 * @property array|null $images
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property string|null $location
 * @property string $status
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FleetMarketListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'brand',
        'model',
        'machine_type',
        'year',
        'price',
        'currency',
        'condition',
        'hours_on_machine',
        'description',
        'images',
        'contact_name',
        'contact_email',
        'contact_phone',
        'location',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'price'            => 'float',
        'images'           => 'array',
        'hours_on_machine' => 'integer',
        'year'             => 'integer',
        'expires_at'       => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}

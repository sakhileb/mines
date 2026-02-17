<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelTransaction extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'monthly_allocation_id',
        'fuel_tank_id',
        'machine_id',
        'user_id',
        'transaction_type',
        'quantity_liters',
        'unit_price',
        'total_cost',
        'fuel_type',
        'transaction_date',
        'odometer_reading',
        'machine_hours',
        'supplier',
        'invoice_number',
        'receipt_file_path',
        'from_tank_id',
        'to_tank_id',
        'notes',
    ];

    protected $casts = [
        'quantity_liters' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'odometer_reading' => 'decimal:2',
        'machine_hours' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function fuelTank(): BelongsTo
    {
        return $this->belongsTo(FuelTank::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromTank(): BelongsTo
    {
        return $this->belongsTo(FuelTank::class, 'from_tank_id');
    }

    public function toTank(): BelongsTo
    {
        return $this->belongsTo(FuelTank::class, 'to_tank_id');
    }

    /**
     * Get transaction cost per liter
     */
    public function getCostPerLiterAttribute(): ?float
    {
        if ($this->quantity_liters == 0 || !$this->total_cost) {
            return null;
        }
        return round($this->total_cost / $this->quantity_liters, 2);
    }

    /**
     * Scope for specific transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific fuel type
     */
    public function scopeFuelType($query, string $type)
    {
        return $query->where('fuel_type', $type);
    }
}

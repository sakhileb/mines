<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelBudget extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'mine_area_id',
        'period_type',
        'start_date',
        'end_date',
        'budgeted_amount',
        'budgeted_liters',
        'actual_spent',
        'actual_liters',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budgeted_amount' => 'decimal:2',
        'budgeted_liters' => 'decimal:2',
        'actual_spent' => 'decimal:2',
        'actual_liters' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    /**
     * Get budget utilization percentage (money)
     */
    public function getBudgetUtilizationAttribute(): float
    {
        if ($this->budgeted_amount == 0) {
            return 0;
        }
        return round(($this->actual_spent / $this->budgeted_amount) * 100, 2);
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudgetAttribute(): float
    {
        return $this->budgeted_amount - $this->actual_spent;
    }

    /**
     * Get volume utilization percentage
     */
    public function getVolumeUtilizationAttribute(): ?float
    {
        if (!$this->budgeted_liters || $this->budgeted_liters == 0) {
            return null;
        }
        return round(($this->actual_liters / $this->budgeted_liters) * 100, 2);
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded(): bool
    {
        return $this->actual_spent > $this->budgeted_amount;
    }

    /**
     * Check if budget is near limit (>90%)
     */
    public function isNearLimit(): bool
    {
        return $this->budget_utilization >= 90 && $this->budget_utilization < 100;
    }

    /**
     * Scope for active budgets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for exceeded budgets
     */
    public function scopeExceeded($query)
    {
        return $query->whereRaw('actual_spent > budgeted_amount');
    }

    /**
     * Scope for current period
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}

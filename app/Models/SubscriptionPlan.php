<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subscription Plan Model
 * 
 * Represents available subscription tiers (Basic, Pro, Enterprise)
 * Defines features and limits for each plan
 */
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'stripe_price_id',
        'stripe_yearly_price_id',
        'features',
        'max_machines',
        'max_users',
        'max_geofences',
        'max_mine_areas',
        'has_advanced_analytics',
        'has_api_access',
        'has_priority_support',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'yearly_price' => 'float',
        'features' => 'array',
        'max_machines' => 'integer',
        'max_users' => 'integer',
        'max_geofences' => 'integer',
        'max_mine_areas' => 'integer',
        'has_advanced_analytics' => 'boolean',
        'has_api_access' => 'boolean',
        'has_priority_support' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Calculate yearly savings percentage
     */
    public function getYearlySavingsPercentageAttribute(): int
    {
        if (!$this->yearly_price || $this->price <= 0) {
            return 0;
        }

        $monthlyTotal = $this->price * 12;
        $savings = $monthlyTotal - $this->yearly_price;
        
        return (int) round(($savings / $monthlyTotal) * 100);
    }

    /**
     * Get monthly price for display
     */
    public function getMonthlyPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Get yearly price for display
     */
    public function getYearlyPriceDisplayAttribute(): string
    {
        return $this->yearly_price ? number_format($this->yearly_price, 2) : '0.00';
    }

    /**
     * Scope query to active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Check if plan has specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }
}

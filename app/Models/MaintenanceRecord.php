<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory, HasTeamFilters;

    protected $fillable = [
        'team_id',
        'machine_id',
        'maintenance_schedule_id',
        'work_order_number',
        'maintenance_type',
        'title',
        'description',
        'work_performed',
        'status',
        'priority',
        'scheduled_date',
        'started_at',
        'completed_at',
        'assigned_to',
        'completed_by',
        'labor_hours',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'parts_used',
        'fault_codes_cleared',
        'odometer_reading',
        'hour_meter_reading',
        'technician_notes',
        'attachments',
        'machine_operational',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'labor_hours' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'parts_used' => 'json',
        'fault_codes_cleared' => 'json',
        'attachments' => 'json',
        'machine_operational' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            if (!$record->work_order_number) {
                $record->work_order_number = 'WO-' . strtoupper(uniqid());
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function maintenanceSchedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get duration in hours
     */
    public function getDurationAttribute(): ?float
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        return $this->started_at->diffInHours($this->completed_at, true);
    }

    /**
     * Scope for completed records
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for in progress
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}

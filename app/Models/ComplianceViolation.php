<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'violation_type',
        'description',
        'severity',
        'detected_at',
        'remediation_deadline',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'metadata',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'remediation_deadline' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the team that owns the violation.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who resolved the violation.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Check if violation is resolved.
     */
    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    /**
     * Check if violation is overdue.
     */
    public function isOverdue(): bool
    {
        return !$this->isResolved() 
            && $this->remediation_deadline 
            && $this->remediation_deadline->isPast();
    }
}

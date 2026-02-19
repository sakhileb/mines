<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shifts';

    protected $fillable = [
        'team_id',
        'shift_type',
        'started_at',
        'ended_at',
        'previous_assignments',
        'productivity_metrics',
        'performance_summary',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'previous_assignments' => 'array',
        'productivity_metrics' => 'array',
        'performance_summary' => 'array',
        'metadata' => 'array',
    ];
}

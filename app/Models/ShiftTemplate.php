<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftTemplate extends Model
{
    use HasTeamFilters;

    protected $fillable = [
        'team_id',
        'category',
        'title',
        'template_body',
        'required_fields',
        'created_by',
    ];

    protected $casts = [
        'required_fields' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

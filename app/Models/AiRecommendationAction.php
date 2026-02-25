<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeamFilters;

class AiRecommendationAction extends Model
{
    use HasFactory, HasTeamFilters;

    protected $table = 'ai_recommendation_actions';

    protected $fillable = [
        'team_id',
        'recommendation_hash',
        'recommendation',
        'status',
        'actioned_by',
        'actioned_at',
        'reject_reason',
        'performance_impact',
    ];

    protected $casts = [
        'recommendation' => 'json',
        'performance_impact' => 'json',
        'actioned_at' => 'datetime',
    ];
}

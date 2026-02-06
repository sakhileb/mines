<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'type',
        'title',
        'message',
        'alert_level',
        'data',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'json',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function readBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_read')
            ->withPivot('read_at');
    }

    public function markAsRead($userId)
    {
        $this->readBy()->attach($userId);
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function isCritical(): bool
    {
        return $this->alert_level === 'critical';
    }

    public function isUrgent(): bool
    {
        return in_array($this->alert_level, ['critical', 'high']);
    }
}

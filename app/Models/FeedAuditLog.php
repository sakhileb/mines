<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeedAuditLog
 *
 * Immutable log of admin actions on feed content.
 *
 * @property int    $id
 * @property int    $team_id
 * @property int    $actor_id
 * @property string $action         pin|unpin|admin_delete|override_approval|invite_sent|go_live_set
 * @property string $subject_type
 * @property int    $subject_id
 * @property array|null $meta
 * @property \Carbon\Carbon $created_at
 */
class FeedAuditLog extends Model
{
    public $timestamps = false;

    public const ACTIONS = [
        'pin'               => 'Pinned post',
        'unpin'             => 'Unpinned post',
        'admin_delete'      => 'Admin deleted post',
        'override_approval' => 'Overrode approval',
        'invite_sent'       => 'Sent onboarding invite',
        'go_live_set'       => 'Set go-live date',
    ];

    protected $fillable = [
        'team_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'meta',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function record(string $action, Model $subject, ?array $meta = null): static
    {
        return static::create([
            'team_id'      => auth()->user()->current_team_id,
            'actor_id'     => auth()->id(),
            'action'       => $action,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'meta'         => $meta,
        ]);
    }
}

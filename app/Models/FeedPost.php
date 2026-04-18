<?php

namespace App\Models;

use App\Traits\HasTeamFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FeedPost Model
 *
 * Represents a structured activity stream post scoped to a team (mine).
 *
 * @property int $id
 * @property int $team_id
 * @property int $author_id
 * @property int|null $mine_area_id
 * @property string|null $shift  A | B | C
 * @property string $category  breakdown | shift_update | safety_alert | production | general
 * @property string $priority  normal | high | critical
 * @property string $body
 * @property array|null $meta  Category-specific structured fields
 * @property int $like_count
 * @property int $comment_count
 * @property int $acknowledgement_count
 * @property bool $is_pinned
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class FeedPost extends Model
{
    use HasFactory, HasTeamFilters, SoftDeletes;

    public const CATEGORIES = [
        'breakdown',
        'shift_update',
        'safety_alert',
        'production',
        'general',
    ];

    public const PRIORITIES = ['normal', 'high', 'critical'];

    public const SHIFTS = ['A', 'B', 'C'];

    protected $fillable = [
        'team_id',
        'author_id',
        'mine_area_id',
        'shift',
        'category',
        'priority',
        'body',
        'meta',
        'like_count',
        'comment_count',
        'acknowledgement_count',
        'is_pinned',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_pinned' => 'boolean',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'acknowledgement_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class, 'mine_area_id');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(FeedAcknowledgement::class, 'post_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FeedAttachment::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id')->whereNull('parent_comment_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class, 'post_id');
    }

    public function approval(): HasOne
    {
        return $this->hasOne(FeedApproval::class, 'post_id');
    }
}

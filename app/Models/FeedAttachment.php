<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeedAttachment Model
 *
 * @property int $id
 * @property int $post_id
 * @property string $file_url
 * @property string $file_type
 * @property string|null $file_name
 * @property int|null $file_size
 * @property \Carbon\Carbon $uploaded_at
 */
class FeedAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'file_url',
        'file_type',
        'file_name',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->file_type, 'audio/');
    }
}

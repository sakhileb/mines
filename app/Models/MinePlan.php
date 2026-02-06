<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MinePlan Model
 * 
 * Represents uploaded mine plans (PDF, DWG, DXF, images) for a mine area.
 * Tracks file information, versions, and metadata.
 */
class MinePlan extends Model
{
    use HasFactory;

    protected $table = 'mine_plans';

    protected $fillable = [
        'mine_area_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_size',
        'file_type', // pdf, dwg, dxf, png, jpg
        'version',
        'title',
        'description',
        'scale',
        'reference_point_lat',
        'reference_point_lon',
        'rotation_degrees',
        'is_current',
        'status', // active, archived
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'scale' => 'float',
        'reference_point_lat' => 'float',
        'reference_point_lon' => 'float',
        'rotation_degrees' => 'float',
        'is_current' => 'boolean',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the mine area this plan belongs to.
     */
    public function mineArea(): BelongsTo
    {
        return $this->belongsTo(MineArea::class);
    }

    /**
     * Get the user who uploaded this plan.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_current', true)->where('status', 'active');
    }

    /**
     * Scope to a specific file type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Mark this plan as current and archive previous versions.
     */
    public function markAsCurrent(): void
    {
        // Archive previous current versions
        self::where('mine_area_id', $this->mine_area_id)
            ->where('id', '!=', $this->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Mark this as current
        $this->update(['is_current' => true]);
    }

    /**
     * Get the file URL for download/preview.
     */
    public function getFileUrl(): string
    {
        return route('mine-plans.download', $this);
    }

    /**
     * Get the preview URL for image files.
     */
    public function getPreviewUrl(): ?string
    {
        if (in_array($this->file_type, ['png', 'jpg'])) {
            return route('mine-plans.preview', $this);
        }

        return null;
    }
}

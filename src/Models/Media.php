<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Rjcodes\Rjcms\Database\Factories\MediaFactory;

/**
 * Standalone media library record. Referenced elsewhere by `id` only.
 */
#[Fillable([
    'name',
    'file_name',
    'disk',
    'path',
    'thumbnail_path',
    'mime_type',
    'extension',
    'size',
    'alt_text',
    'width',
    'height',
    'uploaded_by',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return MediaFactory::new();
    }

    protected $table = 'medias';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * The user who uploaded the file.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Publicly accessible URL for the stored file.
     *
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk($this->disk)->url($this->path));
    }

    /**
     * URL of the compressed thumbnail, falling back to the original when none
     * was generated. Prefer this in lists/grids for faster, optimized loading.
     *
     * @return Attribute<string, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk($this->disk)->url($this->thumbnail_path ?: $this->path));
    }

    /**
     * Whether the stored file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}

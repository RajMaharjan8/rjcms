<?php

namespace Rjcodes\Rjcms\Actions;

use Illuminate\Http\UploadedFile;
use Rjcodes\Rjcms\Models\Media;

/**
 * Persists an uploaded file to the media library.
 */
class StoreMedia
{
    /**
     * Store an uploaded file on the public disk and create its media record.
     *
     * @param  array<string, mixed>  $attributes  Overrides for the media record.
     */
    public function __construct(private GenerateThumbnail $generateThumbnail = new GenerateThumbnail) {}

    /**
     * Store an uploaded file on the public disk and create its media record.
     *
     * @param  array<string, mixed>  $attributes  Overrides for the media record.
     */
    public function __invoke(UploadedFile $file, array $attributes = []): Media
    {
        $dimensions = @getimagesize($file->getRealPath()) ?: [];
        $path = $file->store('media', 'public');
        $thumbnailPath = ($this->generateThumbnail)($file, basename($path));

        return Media::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => basename($path),
            'disk' => 'public',
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'uploaded_by' => auth()->id(),
            ...$attributes,
        ]);
    }
}

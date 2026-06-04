<?php

namespace Rjcodes\Rjcms\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Creates a downsized, compressed thumbnail of an uploaded raster image using
 * GD (no extra dependency). Returns the stored thumbnail path, or null when the
 * file is not a supported raster image or GD is unavailable.
 */
class GenerateThumbnail
{
    /** Maximum width/height of the generated thumbnail, in pixels. */
    private const MAX_DIMENSION = 400;

    /**
     * @param  string  $basename  Filename to use for the thumbnail (matches the original's).
     */
    public function __invoke(UploadedFile $file, string $basename, string $disk = 'public'): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $path = $file->getRealPath();
        $info = @getimagesize($path);

        if (! $info) {
            return null;
        }

        [$width, $height] = $info;
        $type = $info[2];

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $source) {
            return null;
        }

        $scale = min(1, self::MAX_DIMENSION / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            imagefilledrectangle($thumb, 0, 0, $targetWidth, $targetHeight, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, null, 70),
            IMAGETYPE_PNG => imagepng($thumb, null, 6),
            IMAGETYPE_GIF => imagegif($thumb),
            IMAGETYPE_WEBP => imagewebp($thumb, null, 70),
            default => null,
        };
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($thumb);

        if ($contents === false || $contents === '') {
            return null;
        }

        $thumbnailPath = 'media/thumbnails/'.$basename;
        Storage::disk($disk)->put($thumbnailPath, $contents);

        return $thumbnailPath;
    }
}

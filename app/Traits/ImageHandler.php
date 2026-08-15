<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ImageHandler
{
    /**
     * Create a processed image and a thumbnail using GD library.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string|null $oldFile
     * @return array [image_path, thumbnail_path]
     */
    public function processItemImage($file, $directory = 'items', $oldFile = null)
    {
        if ($oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

        $filename = time() . '_' . uniqid() . '.webp';
        $thumbFilename = 'thumb_' . $filename;
        $path = $directory . '/' . $filename;
        $thumbPath = $directory . '/' . $thumbFilename;

        // Fallback if GD is not enabled
        if (!function_exists('imagecreatefromstring')) {
            \Illuminate\Support\Facades\Log::error("GD extension is missing. Saving original image without processing.");
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($directory, $filename, 'public');
            return [$path, $path]; // Use same path for thumb as fallback
        }

        $source = \imagecreatefromstring(file_get_contents($file->path()));
        if (!$source) return [null, null];

        $width = \imagesx($source);
        $height = \imagesy($source);

        // Process Main Image (Optimized quality)
        \ob_start();
        \imagewebp($source, null, 85); // 85% quality for main
        $mainContent = \ob_get_clean();
        Storage::disk('public')->put($path, $mainContent);

        // Process Thumbnail (Square Crop for Homepage Layout)
        $size = min($width, $height);
        $thumb = \imagecreatetruecolor(600, 600);
        
        // Transparent/White background for PNGs
        $white = \imagecolorallocate($thumb, 255, 255, 255);
        \imagefill($thumb, 0, 0, $white);

        \imagecopyresampled(
            $thumb, $source,
            0, 0, ($width - $size) / 2, ($height - $size) / 2,
            600, 600, $size, $size
        );

        \ob_start();
        \imagewebp($thumb, null, 90); // 90% quality for best view
        $thumbContent = \ob_get_clean();
        Storage::disk('public')->put($thumbPath, $thumbContent);

        \imagedestroy($source);
        \imagedestroy($thumb);

        return [$path, $thumbPath];
    }
}

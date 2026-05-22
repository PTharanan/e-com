<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait CanProcessImages
{
    /**
     * Process an image: Resize and convert to WebP.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder Destination folder inside public/
     * @param string $prefix Filename prefix
     * @param int $maxWidth Max width to resize to
     * @param int $quality Compression quality (0-100)
     * @return string Relative URL to the processed image
     */
    protected function processImage($file, $folder, $prefix = 'img', $maxWidth = 1200, $quality = 80)
    {
        $directory = 'media/' . trim($folder, '/');
        $path = public_path($directory);

        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $prefix));
        $randomNumber = rand(1000, 9999);
        $filename = $cleanName . '_' . $randomNumber . '.webp';
        $fullPath = $path . '/' . $filename;

        // Get image info
        $imageInfo = getimagesize($file->getRealPath());
        if (!$imageInfo) {
            // Fallback if not an image GD can handle
            $originalExt = $file->getClientOriginalExtension();
            $filename = $cleanName . '_' . $randomNumber . '.' . $originalExt;
            $file->move($path, $filename);
            return $directory . '/' . $filename;
        }
        
        $mime = $imageInfo['mime'];

        // Create image from source
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $image = imagecreatefrompng($file->getRealPath());
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            default:
                $originalExt = $file->getClientOriginalExtension();
                $filename = $cleanName . '_' . $randomNumber . '.' . $originalExt;
                $file->move($path, $filename);
                return $directory . '/' . $filename;
        }

        // Resize if too large
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = ($height / $width) * $newWidth;
            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            
            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $tmp;
        }

        // Save as WebP
        imagewebp($image, $fullPath, $quality);
        imagedestroy($image);

        return $directory . '/' . $filename;
    }
}

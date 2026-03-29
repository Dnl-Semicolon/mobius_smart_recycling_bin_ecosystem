<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OutletPhotoService
{
    private const MAX_WIDTH = 1200;

    private const QUALITY = 85;

    /**
     * Process an uploaded outlet photo: resize if oversized, compress as JPEG.
     * Returns the stored path relative to the public disk.
     */
    public function process(UploadedFile $file): string
    {
        $source = $this->createImageFromFile($file);
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > self::MAX_WIDTH) {
            $newHeight = (int) ($height * self::MAX_WIDTH / $width);
            $resized = imagecreatetruecolor(self::MAX_WIDTH, $newHeight);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, self::MAX_WIDTH, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'outlet_');
        imagejpeg($source, $tempPath, self::QUALITY);
        imagedestroy($source);

        $filename = 'outlets/'.uniqid('outlet_').'.jpg';
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        return $filename;
    }

    /**
     * Delete an existing outlet photo from storage.
     */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createImageFromFile(UploadedFile $file): \GdImage
    {
        $mime = $file->getMimeType();
        $path = $file->getPathname();

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };

        if ($image === false) {
            throw new \RuntimeException('Failed to read uploaded image.');
        }

        return $image;
    }
}

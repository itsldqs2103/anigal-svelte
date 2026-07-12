<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(
            extension_loaded('imagick')
                ? new ImagickDriver
                : new GdDriver
        );
    }

    public function processAndStoreImage(
        string $id,
        UploadedFile $file,
        ?array $paths = null,
        array $options = []
    ): array {
        $options = array_merge([
            'type' => 'image',
            'quality' => 90,
            'preview_quality' => 70,
            'thumb_quality' => 70,
            'preview_size' => 1024,
            'thumb_size' => 256,
            'avatar_size' => 256,
        ], $options);

        $image = $this->manager->read($file);

        $width = $image->width();
        $height = $image->height();

        if (! $paths) {
            if ($options['type'] === 'avatar') {
                $paths = [
                    'image' => "avatars/{$id}/{$id}.webp",
                ];
            } else {
                $date = now()->format('Y-m-d');

                $paths = [
                    'image' => "images/{$date}/{$id}/{$id}.webp",
                    'preview' => "images/{$date}/{$id}/{$id}_preview.webp",
                    'thumb' => "images/{$date}/{$id}/{$id}_thumb.webp",
                ];
            }
        }

        if ($options['type'] === 'avatar') {
            $encodedImage = $image
                ->scaleDown(
                    width: $options['avatar_size'],
                    height: $options['avatar_size']
                )
                ->toWebp(quality: $options['thumb_quality']);

            Storage::disk('public')->put($paths['image'], $encodedImage);

            return [
                'paths' => $paths,
                'file_size' => strlen($encodedImage),
                'width' => $width,
                'height' => $height,
            ];
        }

        $encodedImage = $image->toWebp(
            quality: $options['quality']
        );

        if (
            $width > $options['preview_size']
            || $height > $options['preview_size']
        ) {
            $encodedPreview = $image
                ->scaleDown(
                    width: $options['preview_size'],
                    height: $options['preview_size']
                )
                ->toWebp(
                    quality: $options['preview_quality']
                );
        } else {
            $encodedPreview = $image
                ->toWebp(
                    quality: $options['preview_quality']
                );
        }

        $encodedThumb = $image
            ->coverDown(
                width: $options['thumb_size'],
                height: $options['thumb_size']
            )
            ->toWebp(
                quality: $options['thumb_quality']
            );

        Storage::disk('public')->put($paths['image'], $encodedImage);
        Storage::disk('public')->put($paths['preview'], $encodedPreview);
        Storage::disk('public')->put($paths['thumb'], $encodedThumb);

        return [
            'paths' => $paths,
            'file_size' => strlen($encodedImage),
            'width' => $width,
            'height' => $height,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'image_path',
    'image_source',
    'preview_image_path',
    'thumbnail_image_path',
    'file_size',
    'user_id',
    'width',
    'height',
])]
#[Appends([
    'image_path_url',
    'preview_image_path_url',
    'thumbnail_image_path_url',
])]
class Image extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'image_id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            $tag->image_id = Str::uuid();
        });
    }

    protected function versionedImage(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $relativePath = "storage/{$path}";
        $fullPath = public_path($relativePath);

        if (! is_file($fullPath)) {
            return asset($relativePath);
        }

        return asset($relativePath).'?version='.filemtime($fullPath);
    }

    public function getImagePathUrlAttribute(): ?string
    {
        return $this->versionedImage($this->image_path);
    }

    public function getPreviewImagePathUrlAttribute(): ?string
    {
        return $this->versionedImage($this->preview_image_path);
    }

    public function getThumbnailImagePathUrlAttribute(): ?string
    {
        return $this->versionedImage($this->thumbnail_image_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'images_tags', 'image_id', 'tag_id');
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['fullname', 'email', 'password', 'avatar'])]
#[Appends([
    'avatar_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'user_id';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function likedImages()
    {
        return $this->morphedByMany(
            Image::class,
            'likeable',
            'likes',
            'user_id',
            'likeable_id',
            'user_id',
            'image_id'
        );
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
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

    public function getAvatarPathAttribute(): ?string
    {
        return $this->versionedImage($this->avatar);
    }
}

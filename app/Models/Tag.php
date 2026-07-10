<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['tag_name', 'tag_slug_name', 'tag_desc'])]
class Tag extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'tag_id';

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'images_tags', 'tag_id', 'image_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            $tag->tag_id = Str::uuid();
        });

        static::saving(function ($tag) {
            if (! empty($tag->tag_name)) {
                $tag->tag_slug_name = Str::slug($tag->tag_name, '_');
            }
        });
    }
}

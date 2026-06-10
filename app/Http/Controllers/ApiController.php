<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Image;

class ApiController extends Controller
{
    public function allTags()
    {
        return response()->json(
            Tag::oldest('tag_name')->get()
        );
    }

    public function imageSelectedTags(string $id)
    {
        $image = Image::with('tags:tag_id,tag_name')->findOrFail($id);

        $selectedTags = $image->tags->pluck('tag_id');

        return response()->json([
            'selectedTags' => $selectedTags
        ]);
    }

    public function latestTags()
    {
        return response()->json(
            Tag::latest()->limit(5)->get()
        );
    }

    public function latestImages()
    {
        return response()->json(
            Image::with([
                'tags' => function ($query) {
                    $query->orderBy('tag_name', 'asc');
                },
            ])->latest()->limit(5)->get()
        );
    }

    public function randomTags()
    {
        return response()->json(
            Tag::inRandomOrder()->limit(5)->get()
        );
    }

    public function randomImages()
    {
        return response()->json(
            Image::with([
                'tags' => function ($query) {
                    $query->orderBy('tag_name', 'asc');
                },
            ])->inRandomOrder()->limit(5)->get()
        );
    }

    public function stats()
    {
        $totalTags = Tag::count();
        $totalImages = Image::count();
        $totalImagesFilesize = Image::sum('file_size');

        return response()->json([
            'totalTags' => $totalTags,
            'totalImages' => $totalImages,
            'totalImagesFilesize' => $totalImagesFilesize,
        ]);
    }

    public function fetchTag(string $id)
    {
        $tag = Tag::findOrFail($id);

        return response()->json($tag);
    }

    public function fetchImage(string $id)
    {
        $image = Image::with('tags:tag_id,tag_name')->findOrFail($id);

        return response()->json($image);
    }
}

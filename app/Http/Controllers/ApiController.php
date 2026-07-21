<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function addTag(Request $request)
    {
        $request->validate([
            'tag_name' => ['required', 'string', 'max:255', 'unique:tags,tag_name'],
            'tag_desc' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tag_name' => Str::lower(__('translate.tagname')),
            'tag_desc' => Str::lower(__('translate.tagdesc')),
        ]);

        $tag = Tag::create([
            'tag_name' => $request->tag_name,
            'tag_desc' => $request->tag_desc,
        ]);

        return response()->json($tag, 201);
    }

    public function suggestTags(Request $request)
    {
        $query = $request->query('query', '');

        $tags = Tag::where('tag_slug_name', 'like', "%{$query}%")
            ->orWhere('tag_name', 'like', "%{$query}%")
            ->oldest('tag_name')
            ->limit(10)
            ->get();

        return response()->json($tags);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $defaultLimit = 30;
        $defaultSortBy = 'tag_name';
        $defaultOrder = 'oldest';

        $allowedSortBy = ['created_at', 'tag_name'];
        $allowedOrders = ['latest', 'oldest'];
        $allowedLimits = [20, 25, 30, 35, 40];

        $sortBy = $request->input('sort_by', $defaultSortBy);
        $order = $request->input('order', $defaultOrder);
        $perPage = (int) $request->input('per_page', $defaultLimit);
        $startsWith = $request->input('starts_with');

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = $defaultSortBy;
        }

        if (!in_array($order, $allowedOrders)) {
            $order = $defaultOrder;
        }

        if (!in_array($perPage, $allowedLimits)) {
            $perPage = $defaultLimit;
        }

        if ($startsWith) {
            $startsWith = strtoupper($startsWith);

            if (!preg_match('/^[A-Z]$/', $startsWith)) {
                $startsWith = null;
            }
        }

        $direction = $order === 'latest' ? 'desc' : 'asc';

        $tags = Tag::query()
            ->when($startsWith, function ($query, $startsWith) {
                return $query->where('tag_name', 'like', $startsWith . '%');
            })
            ->orderBy($sortBy, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Tag/Home', [
            'tags' => $tags,
            'filters' => [
                'sort_by' => $sortBy,
                'order' => $order,
                'perPage' => $perPage,
                'startsWith' => $startsWith,
                'allowedLimits' => $allowedLimits,
            ],
            'defaults' => [
                'sort_by' => $defaultSortBy,
                'order' => $defaultOrder,
                'perPage' => $defaultLimit,
            ],
        ]);
    }

    public function getAddTag()
    {
        return Inertia::render('Tag/Add');
    }

    public function postAddTag(Request $request)
    {
        $request->validate([
            'tag_name' => ['required', 'string', 'max:255', 'unique:tags,tag_name'],
            'tag_desc' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tag_name' => Str::lower(__('translate.tagname')),
            'tag_desc' => Str::lower(__('translate.tagdesc')),
        ]);

        $tag = new Tag;
        $tag->tag_name = $request->tag_name;
        $tag->tag_desc = $request->tag_desc;

        $tag->save();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.addsuccess', ['attribute' => __('translate.tag')]),
            ],
        ]);

        return to_route('tag.home', ['per_page' => 30, 'sort' => 'oldest', 'starts_with' => null]);
    }

    public function getEditTag(Request $request)
    {
        $tag_id = $request->query('tag_id');

        return Inertia::render('Tag/Edit', [
            'tag_id' => $tag_id,
        ]);
    }

    public function postEditTag(Request $request)
    {
        $id = $request->query('tag_id');

        $tag = Tag::where('tag_id', $id)->firstOrFail();

        $request->validate([
            'tag_name' => ['required', 'string', 'max:255', 'unique:tags,tag_name,' . $tag->tag_id . ',tag_id'],
            'tag_desc' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tag_name' => Str::lower(__('translate.tagname')),
            'tag_desc' => Str::lower(__('translate.tagdesc')),
        ]);

        $tag->tag_name = $request->tag_name;
        $tag->tag_desc = $request->tag_desc;

        $tag->update();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.updatesuccess', ['attribute' => __('translate.tag')]),
            ],
        ]);

        return to_route('tag.home', ['per_page' => 30, 'sort' => 'oldest', 'starts_with' => null]);
    }

    public function postDeleteTag(Request $request)
    {
        $id = $request->query('tag_id');

        $tag = Tag::where('tag_id', $id)->firstOrFail();
        $tag->delete();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.deletesuccess', ['attribute' => __('translate.tag')]),
            ],
        ]);

        return to_route('tag.home', ['per_page' => 30, 'sort' => 'oldest', 'starts_with' => null]);
    }
}

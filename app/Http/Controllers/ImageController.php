<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $defaultLimit = 30;
        $defaultSortBy = 'created_at';
        $defaultOrder = 'latest';

        $allowedSortBy = ['created_at'];
        $allowedOrders = ['latest', 'oldest'];
        $allowedLimits = [20, 25, 30, 35, 40];

        $sortBy = $request->input('sort_by', $defaultSortBy);
        $order = $request->input('order', $defaultOrder);
        $perPage = (int) $request->input('per_page', $defaultLimit);

        if (! in_array($sortBy, $allowedSortBy)) {
            $sortBy = $defaultSortBy;
        }

        if (! in_array($order, $allowedOrders)) {
            $order = $defaultOrder;
        }

        if (! in_array($perPage, $allowedLimits)) {
            $perPage = $defaultLimit;
        }

        $direction = $order === 'latest' ? 'desc' : 'asc';

        $images = Image::with([
            'tags' => function ($query) {
                $query->orderBy('tag_name', 'asc');
            },
        ])
            ->withCount('likes')
            ->withExists([
                'likes as liked' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->orderBy($sortBy, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Image/Home', [
            'images' => $images,
            'filters' => [
                'sort_by' => $sortBy,
                'order' => $order,
                'perPage' => $perPage,
                'allowedLimits' => $allowedLimits,
            ],
            'defaults' => [
                'sort_by' => $defaultSortBy,
                'order' => $defaultOrder,
                'perPage' => $defaultLimit,
            ],
        ]);
    }

    public function getSearch(Request $request)
    {
        $tagSlug = $request->query('tag_slug_name');
        $suggestedTags = Tag::oldest('tag_name')->get();
        $userId = Auth::id();

        if (! $tagSlug) {
            return Inertia::render('Image/Search', [
                'images' => [
                    'data' => [],
                    'total' => 0,
                    'links' => [],
                ],
                'suggestedTags' => $suggestedTags,
                'searchTag' => null,
            ]);
        }

        $tag = Tag::where('tag_slug_name', $tagSlug)->firstOrFail();

        $images = Image::with([
            'tags' => fn ($query) => $query->orderBy('tag_name'),
        ])
            ->withCount('likes')
            ->when(
                $userId,
                fn ($query) => $query->withExists([
                    'likes as liked' => fn ($query) => $query->where('user_id', $userId),
                ]),
                fn ($query) => $query->select('*')->selectRaw('false as liked')
            )
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.tag_id', $tag->tag_id);
            })
            ->latest()
            ->paginate(18)
            ->withQueryString();

        return Inertia::render('Image/Search', [
            'images' => $images,
            'suggestedTags' => $suggestedTags,
            'searchTag' => $tag->tag_name,
        ]);
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

    public function bytesHelper(string $value)
    {
        if (is_numeric($value)) {
            $bytes = (float) $value;
        } else {
            $unit = strtolower(substr($value, -1));
            $number = (float) $value;

            switch ($unit) {
                case 'g':
                    return $number.'GB';
                case 'm':
                    return $number.'MB';
                case 'k':
                    return $number.'KB';
                default:
                    return $number.'B';
            }
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).$units[$i];
    }

    public function getAddImage()
    {
        $countTags = Tag::count();
        $maxUploadFilesize = $this->bytesHelper(config('app.max_upload_filesize'));

        return Inertia::render('Image/Add', [
            'countTags' => $countTags,
            'maxUploadFilesize' => $maxUploadFilesize,
        ]);
    }

    public function postAddImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|min:128|max:20480',
            'image_source' => 'nullable|string|max:255',
            'tag' => 'required|array|min:1',
            'tag.*' => 'exists:tags,tag_id',
        ], [], [
            'image' => Str::lower(__('translate.image')),
            'image_source' => Str::lower(__('translate.image_source')),
            'tag' => Str::lower(__('translate.tag')),
            'tag.*' => Str::lower(__('translate.tag')),
        ]);

        $result = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $id = time();

            $result = $this->processAndStoreImage($id, $file);
        }

        $image = Image::create([
            'image_path' => $result['paths']['image'],
            'image_source' => $request->image_source,
            'preview_image_path' => $result['paths']['preview'],
            'thumbnail_image_path' => $result['paths']['thumb'],
            'file_size' => $result['file_size'],
            'width' => $result['width'],
            'height' => $result['height'],
            'user_id' => Auth::id(),
        ]);

        if ($request->filled('tag')) {
            $image->tags()->sync($request->tag);
        }

        Inertia::flash([
            'toast' => [
                'success' => __('translate.addsuccess', ['attribute' => __('translate.image')]),
            ],
        ]);

        return to_route('image.home', ['per_page' => 30, 'order' => 'latest', 'sort_by' => 'created_at']);
    }

    public function getEditImage(Request $request)
    {
        $image_id = $request->query('image_id');
        $countTags = Tag::count();
        $maxUploadFilesize = $this->bytesHelper(config('app.max_upload_filesize'));

        return Inertia::render('Image/Edit', [
            'image_id' => $image_id,
            'countTags' => $countTags,
            'maxUploadFilesize' => $maxUploadFilesize,
        ]);
    }

    public function postEditImage(Request $request)
    {
        $id = $request->query('image_id');

        $image = Image::where('image_id', $id)->firstOrFail();

        $request->validate([
            'image' => 'nullable|image|min:128|max:20480',
            'image_source' => 'nullable|string|max:255',
            'tag' => 'required|array|min:1',
            'tag.*' => 'exists:tags,tag_id',
        ], [], [
            'image' => Str::lower(__('translate.image')),
            'image_source' => Str::lower(__('translate.image_source')),
            'tag' => Str::lower(__('translate.tag')),
            'tag.*' => Str::lower(__('translate.tag')),
        ]);

        $result = [
            'file_size' => $image->file_size,
            'width' => $image->width,
            'height' => $image->height,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $result = $this->processAndStoreImage($id, $file, [
                'image' => $image->image_path,
                'preview' => $image->preview_image_path,
                'thumb' => $image->thumbnail_image_path,
            ]);
        }

        $image->update([
            'file_size' => $result['file_size'],
            'width' => $result['width'],
            'height' => $result['height'],
            'image_source' => $request->image_source,
        ]);

        if ($request->filled('tag')) {
            $image->tags()->sync($request->tag);
        }

        Inertia::flash([
            'toast' => [
                'success' => __('translate.updatesuccess', ['attribute' => __('translate.image')]),
            ],
        ]);

        return to_route('image.home', ['per_page' => 30, 'order' => 'latest', 'sort_by' => 'created_at']);
    }

    public function postDeleteImage(Request $request)
    {
        $id = $request->query('image_id');

        $image = Image::where('image_id', $id)->firstOrFail();

        $disk = Storage::disk('public');

        $disk->delete([
            $image->image_path,
            $image->preview_image_path,
            $image->thumbnail_image_path,
        ]);

        $directory = dirname($image->image_path);
        $timeDirectory = dirname($directory);

        if ($disk->exists($directory) && count($disk->files($directory)) === 0) {
            $disk->deleteDirectory($directory);
        }

        if (
            $disk->exists($timeDirectory) &&
            count($disk->files($timeDirectory)) === 0 &&
            count($disk->directories($timeDirectory)) === 0
        ) {
            $disk->deleteDirectory($timeDirectory);
        }

        $image->delete();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.deletesuccess', ['attribute' => __('translate.image')]),
            ],
        ]);

        return to_route('image.home', ['per_page' => 30, 'order' => 'latest', 'sort_by' => 'created_at']);
    }

    private function processAndStoreImage(
        int $id,
        UploadedFile $file,
        ?array $paths = null
    ) {
        $manager = new ImageManager(
            extension_loaded('imagick')
                ? new ImagickDriver
                : new GdDriver
        );

        $image = $manager->read($file);

        $width = $image->width();
        $height = $image->height();

        if (! $paths) {
            $time = now()->format('Y-m-d');

            $paths = [
                'image' => "images/{$time}/{$id}/{$id}.webp",
                'preview' => "images/{$time}/{$id}/{$id}_preview.webp",
                'thumb' => "images/{$time}/{$id}/{$id}_thumb.webp",
            ];
        }

        $encodedImage = $image->toWebp(quality: 90);

        if ($width > 1024 || $height > 1024) {
            $encodedPreview = $image->scaleDown(width: 1024, height: 1024)->toWebp(quality: 70);
        } else {
            $encodedPreview = $image->toWebp(quality: 70);
        }

        $encodedThumb = $image->coverDown(
            width: 256,
            height: 256
        )->toWebp(quality: 70);

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

    public function likeImage(Image $image)
    {
        $user = Auth::user();

        $like = $image->likes()
            ->where('user_id', $user->user_id)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            $image->likes()->create([
                'user_id' => $user->user_id,
            ]);
        }
    }
}

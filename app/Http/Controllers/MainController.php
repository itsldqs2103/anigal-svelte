<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        $randomTags = Tag::inRandomOrder()->limit(5)->get();
        $latestTags = Tag::latest()->limit(5)->get();
        $randomImages = Image::with([
            'tags' => function ($query) {
                $query->orderBy('tag_name', 'asc');
            },
        ])->inRandomOrder()->limit(5)->get();
        $latestImages = Image::with(['tags' => function ($query) {
            $query->orderBy('tag_name', 'asc');
        }, 'likes'])->latest()->limit(5)->get();

        return Inertia::render('Home', [
            'randomTags' => $randomTags,
            'latestTags' => $latestTags,
            'randomImages' => $randomImages,
            'latestImages' => $latestImages
        ]);
    }

    public function stats()
    {
        $totalTags = Tag::count();
        $totalImages = Image::count();
        $totalImagesFilesize = Image::sum('file_size');

        return Inertia::render('Stats', [
            'totalTags' => $totalTags,
            'totalImages' => $totalImages,
            'totalImagesFilesize' => $totalImagesFilesize
        ]);
    }

    public function setting()
    {
        $supportedLocales = config('i18nlocale.supportedLocales');
        sort($supportedLocales);

        return Inertia::render('Setting', [
            'supportedLocales' => $supportedLocales,
        ]);
    }

    public function locale(Request $request)
    {

        $locale = $request->locale;

        $supportedLocales = array_column(config('i18nlocale.supportedLocales'), 'code');

        if ($locale && in_array($locale, $supportedLocales)) {
            $targetLocale = $locale;
        } elseif (request()->cookie('locale') && in_array(request()->cookie('locale'), $supportedLocales)) {
            $targetLocale = request()->cookie('locale');
        } else {
            $targetLocale = request()->getPreferredLanguage($supportedLocales) ?? 'en';
        }

        $cookie = cookie('locale', $targetLocale, 60 * 24 * 30);

        Inertia::flash([
            'toast' => [
                'success' => __('translate.updatesuccess', ['attribute' => __('translate.language')]),
            ],
        ]);

        return to_route('setting')->withCookie($cookie);
    }

    public function getLocaleFile(string $locale)
    {
        $supportedLocales = array_column(config('i18nlocale.supportedLocales'), 'code');

        if (! in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return response()->json(
            collect(File::files(base_path("lang/$locale")))
                ->mapWithKeys(fn($file) => [
                    pathinfo($file, PATHINFO_FILENAME) => require $file,
                ])
        );
    }

    public function term()
    {
        $lastUpdated = Carbon::parse(
            File::lastModified(base_path('resources/js/Pages/Legal/Term.svelte'))
        )->toDateTimeString();

        return Inertia::render('Legal/Term', [
            'lastUpdated' => $lastUpdated,
        ]);
    }

    public function dmca()
    {
        $lastUpdated = Carbon::parse(
            File::lastModified(base_path('resources/js/Pages/Legal/DMCA.svelte'))
        )->toDateTimeString();

        return Inertia::render('Legal/DMCA', [
            'lastUpdated' => $lastUpdated,
        ]);
    }
}

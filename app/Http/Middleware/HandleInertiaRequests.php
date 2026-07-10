<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'currentAuth' => $request?->user()?->only(['user_id', 'username']),
            'currentPHP' => PHP_VERSION,
            'currentLaravel' => app()->version(),
            'currentLocale' => app()->getLocale(),
            'lastUpdated' => Carbon::parse(
                File::lastModified(base_path('resources/js/app.js'))
            )->toDateTimeString(),
        ];
    }
}

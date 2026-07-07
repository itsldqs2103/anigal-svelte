<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class UserController extends Controller
{
    public function getLogin()
    {
        return Inertia::render('User/Login');
    }

    public function postLogin(LoginRequest $request)
    {
        $request->authenticate();
        Auth::logoutOtherDevices($request->password);
        $request->session()->regenerate();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.loggedin'),
            ],
        ]);

        return to_route('home');
    }

    public function postLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.loggedout'),
            ],
        ]);

        return to_route('login');
    }

    public function profile(Request $request)
    {
        $id = $request->query('user_id');
        $tab = $request->query('tab', 'uploaded');

        $user = User::where('user_id', $id)
            ->select([
                'user_id',
                'username',
                'email',
                'fullname',
                'avatar',
                'created_at'
            ])
            ->firstOrFail();

        $user->created_at_diff = $user->created_at->diffForHumans();

        $countUploaded = Image::where('user_id', $user->user_id)->count();

        $countLiked = Image::whereHas('likes', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->count();

        if ($tab === 'liked') {
            $likedImages = Inertia::scroll(
                fn() => Image::with('tags')
                    ->withCount('likes')
                    ->withExists([
                        'likes as liked' => fn($query) => $query->where('user_id', $id),
                    ])
                    ->whereHas('likes', function ($query) use ($user) {
                        $query->where('user_id', $user->user_id);
                    })
                    ->paginate(15)
            );

            return Inertia::render('User/Liked', [
                'user' => $user,
                'likedImages' => $likedImages,
                'countUploaded' => $countUploaded,
                'countLiked' => $countLiked,
            ]);
        }

        $uploadedImages = Inertia::scroll(
            fn() => Image::with('tags')
                ->withCount('likes')
                ->withExists([
                    'likes as liked' => fn($query) => $query->where('user_id', $id),
                ])
                ->where('user_id', $user->user_id)
                ->paginate(15)
        );

        return Inertia::render('User/Uploaded', [
            'user' => $user,
            'uploadedImages' => $uploadedImages,
            'countUploaded' => $countUploaded,
            'countLiked' => $countLiked,
        ]);
    }

    public function getEditProfile(Request $request)
    {
        $id = $request->query('user_id');
        $user = User::where('user_id', $id)->select(['user_id', 'username', 'email', 'fullname'])->firstOrFail();

        return Inertia::render('User/Edit/Profile', [
            'user' => $user,
        ]);
    }

    public function postEditProfile(Request $request)
    {
        $id = $request->query('user_id');
        $user = User::where('user_id', $id)->select(['user_id', 'username', 'email', 'fullname'])->firstOrFail();

        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username,' . $user->user_id . ',user_id',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->user_id . ',user_id',
            ],
        ], [], [
            'fullname' => Str::lower(__('translate.fullname')),
            'username' => Str::lower(__('translate.username')),
            'email' => Str::lower(__('translate.email')),
        ]);

        $user->fullname = $request->fullname;
        $user->email = $request->email;

        $user->update();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.updatesuccess', ['attribute' => __('translate.profile')]),
            ],
        ]);

        return to_route('profile', ['user_id' => $user->user_id]);
    }

    public function getEditPassword(Request $request)
    {
        $id = $request->query('user_id');
        $user = User::where('user_id', $id)->select(['user_id'])->firstOrFail();

        return Inertia::render('User/Edit/Password', [
            'user' => $user,
        ]);
    }

    public function postEditPassword(Request $request)
    {
        $id = $request->query('user_id');
        $user = User::where('user_id', $id)->select(['user_id'])->firstOrFail();

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ], [], [
            'password' => Str::lower(__('translate.password')),
        ]);

        $user->password = Hash::make($request->password);

        $user->update();

        Inertia::flash([
            'toast' => [
                'success' => __('translate.updatesuccess', ['attribute' => __('translate.password')]),
            ],
        ]);

        return to_route('profile', ['user_id' => $user->user_id]);
    }

    private function processAndStoreImage($id, $file, $paths = null)
    {
        $manager = new ImageManager(
            extension_loaded('imagick')
                ? new ImagickDriver()
                : new GdDriver()
        );

        $image = $manager->read($file);

        $width = $image->width();
        $height = $image->height();

        if (! $paths) {
            $paths = [
                'image' => "avatars/{$id}/{$id}.webp",
            ];
        }

        $encodedImage = $image->scaleDown(width: 256, height: 256)->toWebp(quality: 70);

        Storage::disk('public')->put($paths['image'], $encodedImage);

        return [
            'paths' => $paths,
            'file_size' => strlen($encodedImage),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function postEditAvatar(Request $request)
    {
        $id = $request->query('user_id');

        $user = User::where('user_id', $id)->firstOrFail();

        $request->validate([
            'avatar' => ['required', 'image', 'max:20480'],
        ], [], [
            'avatar' => __('translate.avatar'),
        ]);

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $result = $this->processAndStoreImage(
            $user->user_id,
            $request->file('avatar')
        );

        $user->update([
            'avatar' => $result['paths']['image'],
        ]);

        return to_route('profile', ['user_id' => $user->user_id]);
    }

    public function postDeleteAvatar(Request $request)
    {
        $id = $request->query('user_id');

        $user = User::where('user_id', $id)->firstOrFail();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => null,
        ]);

        return to_route('profile', ['user_id' => $user->user_id]);
    }
}

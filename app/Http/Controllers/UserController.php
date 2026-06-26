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
        $user = User::where('user_id', $id)->select(['user_id', 'username', 'email', 'fullname'])->firstOrFail();

        $likedImages = Image::with('tags')->withCount('likes')
            ->withExists([
                'likes as liked' => fn($query) => $query->where('user_id', $id),
            ])->whereHas('likes', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->get();

        $uploadedImages = Image::with('tags')->withCount('likes')
            ->withExists([
                'likes as liked' => fn($query) => $query->where('user_id', $id),
            ])->where('user_id', $user->user_id)->get();

        return Inertia::render('User/Profile', [
            'user' => $user,
            'likedImages' => $likedImages,
            'uploadedImages' => $uploadedImages,
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
}

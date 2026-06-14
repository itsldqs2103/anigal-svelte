<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\ApiMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('/tag', [TagController::class, 'index'])->name('tag.home');
Route::get('/image', [ImageController::class, 'index'])->name('image.home');
Route::get('/image/search', [ImageController::class, 'getSearch'])->name('image.search');
Route::get('/setting', [MainController::class, 'setting'])->name('setting');
Route::post('/locale', [MainController::class, 'locale']);
Route::get('/api/locale/{locale}.json', [MainController::class, 'getLocaleFile']);
Route::get('/optimize/clear', [MainController::class, 'clearOptimize'])->name('optimize.clear');
Route::get('/term', [MainController::class, 'term'])->name('term');
Route::get('/dmca', [MainController::class, 'dmca'])->name('dmca');
Route::get('/stats', [MainController::class, 'stats'])->name('stats');

Route::middleware(ApiMiddleware::class)
    ->prefix('api')
    ->controller(ApiController::class)
    ->group(function () {

        Route::get('/tag/{tag}', 'fetchTag')->name('api.tag.fetch');
        Route::get('/tags/suggest', 'suggestTags')->name('api.tags.suggest');

        Route::get('/image/{image}', 'fetchImage')->name('api.image.fetch');

        Route::get('/tags/latest', 'latestTags')->name('api.tags.latest');
        Route::get('/images/latest', 'latestImages')->name('api.images.latest');

        Route::get('/tags/random', 'randomTags')->name('api.tags.random');
        Route::get('/images/random', 'randomImages')->name('api.images.random');

        Route::get('/stats', 'stats')->name('api.stats');

        Route::get('/tags/all', 'allTags')->name('api.tags.all');
        Route::get('/images/{image}/tags', 'imageSelectedTags')->name('api.images.tags');

        Route::post('/tag/add', 'addTag')->name('api.tag.add');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [UserController::class, 'getEditProfile'])->name('profile.edit');
    Route::post('/profile/edit', [UserController::class, 'postEditProfile']);
    Route::get('/password/edit', [UserController::class, 'getEditPassword'])->name('password.edit');
    Route::post('/password/edit', [UserController::class, 'postEditPassword']);
    Route::post('/logout', [UserController::class, 'postLogout']);
    Route::get('/tag/add', [TagController::class, 'getAddTag'])->name('tag.add');
    Route::post('/tag/add', [TagController::class, 'postAddTag']);
    Route::get('/tag/edit', [TagController::class, 'getEditTag'])->name('tag.edit');
    Route::post('/tag/edit', [TagController::class, 'postEditTag']);
    Route::delete('/tag/delete', [TagController::class, 'postDeleteTag']);
    Route::get('/image/add', [ImageController::class, 'getAddImage'])->name('image.add');
    Route::post('/image/add', [ImageController::class, 'postAddImage']);
    Route::get('/image/edit', [ImageController::class, 'getEditImage'])->name('image.edit');
    Route::post('/image/edit', [ImageController::class, 'postEditImage']);
    Route::delete('/image/delete', [ImageController::class, 'postDeleteImage']);
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [UserController::class, 'getLogin'])->name('login');
    Route::post('/login', [UserController::class, 'postLogin']);
});

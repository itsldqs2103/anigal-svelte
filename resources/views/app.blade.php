<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="invisible pointer-events-none">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="robots" content="index, follow" />
    <meta name="author" content="{{ config('app.name') }}" />
    <meta name="description" content="{{ __('translate.description', ['appname' => config('app.name')]) }}" />
    <meta name="rating" content="adult" />
    <meta name="google" value="notranslate" />

    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
    <meta name="theme-color" content="#15191e" />

    <link rel="icon" href="{{ Vite::asset('resources/images/anigal-logo.webp') }}" />
    <link rel="apple-touch-icon" href="{{ Vite::asset('resources/images/anigal-logo.webp') }}" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/js/app.js')
        <x-inertia::head />
    @endif
</head>

<body>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <x-inertia::app />
    @endif
</body>

</html>

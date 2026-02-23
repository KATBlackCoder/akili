<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="fantasy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Connexion' }} — Akili</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 bg-primary rounded-2xl items-center justify-center mb-4">
                <span class="text-primary-content font-bold text-3xl">A</span>
            </div>
            <h1 class="text-3xl font-bold">Akili</h1>
            <p class="text-base-content/60 mt-1">Gestion RH & Questionnaires Terrain</p>
        </div>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>

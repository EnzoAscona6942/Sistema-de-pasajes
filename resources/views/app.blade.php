<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts de Vite -->
        @vite(['resources/js/app.js', 'resources/css/app.css'])
        
        <!-- Directiva de Inertia -->
        @inertiaHead
        
        <!-- Rutas de Laravel en JS (Ziggy) -->
        @routes
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
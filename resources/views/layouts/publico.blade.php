<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Alumni Connect EFPISC')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    <header class="bg-institucional-700 text-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('inicio') }}" class="text-lg font-semibold tracking-tight">
                Alumni Connect EFPISC
            </a>
            <a href="{{ route('login') }}" class="rounded px-3 py-2 text-sm font-medium hover:bg-institucional-600">
                Iniciar sesión
            </a>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center px-4 py-14 sm:px-6 lg:px-8">
        @yield('contenido')
    </main>

    @include('layouts.partials.pie-pagina')

</body>
</html>

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
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3">
                <a href="{{ route('egresado.perfil') }}" class="text-lg font-semibold tracking-tight">
                    Alumni Connect EFPISC
                </a>

                <label for="menu-egresado" class="cursor-pointer rounded p-2 hover:bg-institucional-600 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="sr-only">Abrir menú</span>
                </label>
            </div>

            <input type="checkbox" id="menu-egresado" class="peer hidden">

            <nav class="hidden flex-col gap-1 pb-4 peer-checked:flex lg:flex lg:flex-row lg:items-center lg:gap-2 lg:pb-3">
                <a href="{{ route('egresado.perfil') }}"
                   class="rounded px-3 py-2 text-sm font-medium hover:bg-institucional-600 {{ request()->routeIs('egresado.perfil') ? 'bg-institucional-800' : '' }}">
                    Mi perfil
                </a>
                <a href="{{ route('egresado.ofertas') }}"
                   class="rounded px-3 py-2 text-sm font-medium hover:bg-institucional-600 {{ request()->routeIs('egresado.ofertas') ? 'bg-institucional-800' : '' }}">
                    Bolsa de trabajo
                </a>

                <span class="mx-1 hidden text-institucional-300 lg:inline">|</span>

                <span class="px-3 py-2 text-sm text-institucional-100">
                    {{ auth()->user()->egresado?->nombre_completo ?? auth()->user()->email }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded px-3 py-2 text-left text-sm font-medium hover:bg-institucional-600 lg:w-auto lg:text-center">
                        Cerrar sesión
                    </button>
                </form>
            </nav>
        </div>
    </header>

    @include('layouts.partials.mensajes-flash')

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
        @yield('contenido')
    </main>

    @include('layouts.partials.pie-pagina')

</body>
</html>

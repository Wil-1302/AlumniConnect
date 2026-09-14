<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Alumni Connect EFPISC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    <header class="bg-institucional-700 text-white">
        <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('inicio') }}" class="text-lg font-semibold tracking-tight">
                Alumni Connect EFPISC
            </a>
        </div>
    </header>

    @include('layouts.partials.mensajes-flash')

    <main class="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full max-w-sm rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-xl font-semibold text-slate-900">Iniciar sesión</h1>
            <p class="mt-1 text-sm text-slate-500">Ingresa con tu correo institucional.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" novalidate>
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" name="email" id="email" required autofocus
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                    <input type="password" name="password" id="password" required
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="recordar" value="1"
                           class="rounded border-slate-300 text-institucional-600 focus:ring-institucional-500">
                    Recordarme
                </label>

                <button type="submit"
                        class="w-full rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
                    Ingresar
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                ¿Aún no tienes cuenta?
                <a href="{{ route('registro') }}" class="font-medium text-institucional-700 hover:underline">Regístrate</a>
            </p>
        </div>
    </main>

    @include('layouts.partials.pie-pagina')

</body>
</html>

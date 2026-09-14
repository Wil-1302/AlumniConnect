<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Connect EFPISC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    <header class="bg-institucional-700 text-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <span class="text-lg font-semibold tracking-tight">Alumni Connect EFPISC</span>
            <a href="{{ route('login') }}" class="rounded px-3 py-2 text-sm font-medium hover:bg-institucional-600">
                Iniciar sesión
            </a>
        </div>
    </header>

    <main class="flex-1">
        <section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    La plataforma de vinculación de egresados de Ingeniería de Sistemas y Computación
                </h1>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Alumni Connect EFPISC conecta a los egresados de la Escuela de Formación Profesional
                    de Ingeniería de Sistemas y Computación de la UNDAC con su casa de estudios: actualiza
                    tu situación laboral, accede a la bolsa de trabajo, participa en encuestas de
                    seguimiento y mantente en contacto con tu comunidad académica.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('registro') }}"
                       class="w-full rounded-md bg-institucional-700 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
                        Registrarme
                    </a>
                    <a href="{{ route('login') }}"
                       class="w-full rounded-md border border-institucional-700 px-6 py-3 text-center text-sm font-semibold text-institucional-700 hover:bg-institucional-50 sm:w-auto">
                        Iniciar sesión
                    </a>
                </div>
            </div>

            <div class="mx-auto mt-16 grid max-w-4xl gap-6 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold text-slate-900">Perfil actualizado</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Registra tu situación laboral y mantén tu información visible para la escuela.
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold text-slate-900">Bolsa de trabajo</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Encuentra ofertas laborales publicadas específicamente para la comunidad de egresados.
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold text-slate-900">Encuestas de seguimiento</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Participa en los estudios de inserción laboral que impulsan la mejora del programa.
                    </p>
                </div>
            </div>
        </section>
    </main>

    @include('layouts.partials.pie-pagina')

</body>
</html>

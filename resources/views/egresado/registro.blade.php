<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crea tu cuenta — Alumni Connect EFPISC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    <header class="bg-institucional-700 text-white">
        <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('inicio') }}" class="text-lg font-semibold tracking-tight">
                Alumni Connect EFPISC
            </a>
        </div>
    </header>

    <main class="mx-auto w-full max-w-3xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md">

            <h1 class="text-2xl font-bold text-slate-900">Crea tu cuenta de egresado</h1>
            <p class="mt-2 text-sm text-slate-600">
                Regístrate para acceder a <strong>ofertas laborales exclusivas para egresados</strong>
                de la Escuela de Formación Profesional de Ingeniería de Sistemas y Computación, y
                mantener actualizado tu perfil ante tu casa de estudios.
            </p>

            <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-institucional-50 px-3 py-1 text-xs font-medium text-institucional-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Te toma menos de 3 minutos
            </p>

            <form method="POST" action="{{ route('registro') }}" class="mt-6 space-y-5" novalidate>
                @csrf

                {{-- 1. DNI --}}
                <div>
                    <label for="dni" class="block text-sm font-medium text-slate-700">
                        Documento de identidad (DNI)
                    </label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="8"
                           name="dni" id="dni" required autofocus
                           value="{{ old('dni') }}"
                           aria-invalid="{{ $errors->has('dni') ? 'true' : 'false' }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('dni') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    @error('dni')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Correo --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email') }}"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('email') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Contraseña
                    </label>
                    <input type="password" name="password" id="password" required minlength="8"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('password') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    <p class="mt-1 text-xs text-slate-500">Mínimo 8 caracteres.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 4. Confirmar contraseña --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                        Confirmar contraseña
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                </div>

                {{-- Datos opcionales: colapsados, no cuentan para los 5 obligatorios --}}
                <details class="rounded-md border border-slate-200 bg-slate-50 p-4">
                    <summary class="cursor-pointer text-sm font-medium text-slate-700">
                        Datos opcionales
                        <span class="font-normal text-slate-500">(teléfono y ciudad)</span>
                    </summary>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="telefono" class="block text-sm font-medium text-slate-700">
                                Teléfono <span class="font-normal text-slate-400">(opcional)</span>
                            </label>
                            <input type="tel" name="telefono" id="telefono"
                                   value="{{ old('telefono') }}"
                                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                          {{ $errors->has('telefono') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                            @error('telefono')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ciudad" class="block text-sm font-medium text-slate-700">
                                Ciudad <span class="font-normal text-slate-400">(opcional)</span>
                            </label>
                            <input type="text" name="ciudad" id="ciudad"
                                   value="{{ old('ciudad') }}"
                                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                          {{ $errors->has('ciudad') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                            @error('ciudad')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </details>

                {{-- 5. Consentimiento --}}
                <div>
                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="consentimiento" value="1" required
                               class="mt-0.5 rounded border-slate-300 text-institucional-600 focus:ring-institucional-500">
                        <span>
                            He leído y acepto la
                            <a href="{{ route('politica') }}" target="_blank" rel="noopener noreferrer"
                               class="font-medium text-institucional-700 hover:underline">
                                política de tratamiento de datos personales
                            </a>.
                        </span>
                    </label>
                    @error('consentimiento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-institucional-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-institucional-800">
                    Crear mi cuenta
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-medium text-institucional-700 hover:underline">Inicia sesión</a>
            </p>
        </div>
    </main>

    @include('layouts.partials.pie-pagina')

</body>
</html>

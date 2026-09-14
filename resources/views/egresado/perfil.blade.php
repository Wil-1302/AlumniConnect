@extends('layouts.app')

@section('titulo', 'Mi perfil — Alumni Connect EFPISC')

@php
    $situacionActual = $egresado->experiencias->firstWhere('es_actual', true);
    $historial = $egresado->experiencias->where('es_actual', false)->sortByDesc('fecha_inicio');

    // La situación seleccionada oculta los campos de detalle laboral cuando
    // su columna requiere_detalle_laboral es false (E-08 numeral 2.2: el
    // dato vive en el catálogo, no se compara texto literal en la vista).
    $situacionSeleccionada = old('situacion_id', $situacionActual?->situacion_id);
    $ocultarDetalle = ! ($situaciones->firstWhere('id', (int) $situacionSeleccionada)?->requiere_detalle_laboral ?? true);
@endphp

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Mi perfil</h1>
    <p class="mt-1 text-sm text-slate-500">
        Última actualización:
        {{ $egresado->actualizado_en?->format('d/m/Y H:i') ?? 'Aún no registra actualizaciones.' }}
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        {{-- Datos personales --}}
        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-slate-900">Datos personales</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $egresado->nombre_completo }} · Promoción {{ $egresado->anio_egreso }}
                · {{ $egresado->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller' }}
            </p>

            <form method="POST" action="{{ route('egresado.perfil.actualizar') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="telefono" class="block text-sm font-medium text-slate-700">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono"
                           value="{{ old('telefono', $egresado->telefono) }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('telefono') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    @error('telefono')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ciudad" class="block text-sm font-medium text-slate-700">Ciudad</label>
                    <input type="text" name="ciudad" id="ciudad"
                           value="{{ old('ciudad', $egresado->ciudad) }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('ciudad') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    @error('ciudad')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="hidden" name="perfil_visible" value="0">
                    <input type="checkbox" name="perfil_visible" value="1"
                           @checked(old('perfil_visible', $egresado->perfil_visible))
                           class="mt-0.5 rounded border-slate-300 text-institucional-600 focus:ring-institucional-500">
                    <span>
                        Mostrar mi perfil en el directorio de la Escuela
                        <span class="block text-xs text-slate-500">
                            Tu correo y teléfono nunca se muestran públicamente, solo tu situación laboral general.
                        </span>
                    </span>
                </label>

                <button type="submit"
                        class="w-full rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
                    Guardar datos personales
                </button>
            </form>
        </section>

        {{-- Situación laboral --}}
        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-slate-900">Situación laboral actual</h2>
            <p class="mt-1 text-sm text-slate-500">
                Registrar una nueva situación reemplaza a la actual; la anterior pasa a tu historial.
            </p>

            <form method="POST" action="{{ route('egresado.situacion.actualizar') }}"
                  id="form-situacion" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="situacion_id" class="block text-sm font-medium text-slate-700">Situación</label>
                    <select name="situacion_id" id="situacion_id" required
                            class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                   {{ $errors->has('situacion_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                        <option value="">Seleccione una situación</option>
                        @foreach ($situaciones as $situacion)
                            <option value="{{ $situacion->id }}"
                                    data-sin-detalle="{{ $situacion->requiere_detalle_laboral ? '0' : '1' }}"
                                    @selected((int) $situacionSeleccionada === $situacion->id)>
                                {{ $situacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('situacion_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="detalle-laboral" class="space-y-4 {{ $ocultarDetalle ? 'hidden' : '' }}">
                    <div>
                        <label for="rubro_id" class="block text-sm font-medium text-slate-700">Rubro</label>
                        <select name="rubro_id" id="rubro_id"
                                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                       {{ $errors->has('rubro_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                            <option value="">Seleccione un rubro</option>
                            @foreach ($rubros as $rubro)
                                <option value="{{ $rubro->id }}"
                                        @selected((int) old('rubro_id') === $rubro->id)>
                                    {{ $rubro->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('rubro_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="empresa" class="block text-sm font-medium text-slate-700">Empresa</label>
                        <input type="text" name="empresa" id="empresa" value="{{ old('empresa') }}"
                               class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                      {{ $errors->has('empresa') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                        @error('empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cargo" class="block text-sm font-medium text-slate-700">Cargo</label>
                        <input type="text" name="cargo" id="cargo" value="{{ old('cargo') }}"
                               class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                      {{ $errors->has('cargo') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                        @error('cargo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-slate-700">Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                           value="{{ old('fecha_inicio') }}" max="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                  {{ $errors->has('fecha_inicio') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    @error('fecha_inicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
                    Actualizar situación laboral
                </button>
            </form>
        </section>
    </div>

    {{-- Historial, orden cronológico inverso --}}
    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-semibold text-slate-900">Historial de situaciones anteriores</h2>

        @if ($historial->isEmpty())
            <p class="mt-3 text-sm text-slate-500">Todavía no tienes situaciones anteriores registradas.</p>
        @else
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($historial as $experiencia)
                    <li class="py-3">
                        <p class="text-sm font-medium text-slate-800">
                            {{ $experiencia->situacion?->nombre ?? 'Situación no registrada' }}
                        </p>
                        <p class="text-sm text-slate-500">
                            @if ($experiencia->empresa)
                                {{ $experiencia->cargo ? "{$experiencia->cargo} en " : '' }}{{ $experiencia->empresa }}
                                @if ($experiencia->rubro) · {{ $experiencia->rubro->nombre }} @endif
                            @endif
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ $experiencia->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha de inicio' }}
                            &ndash;
                            {{ $experiencia->fecha_fin?->format('d/m/Y') ?? 'Sin fecha de fin' }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <script>
        (function () {
            var select = document.getElementById('situacion_id');
            var detalle = document.getElementById('detalle-laboral');

            if (!select || !detalle) {
                return;
            }

            select.addEventListener('change', function () {
                var opcion = select.options[select.selectedIndex];
                var sinDetalle = opcion && opcion.dataset.sinDetalle === '1';
                detalle.classList.toggle('hidden', sinDetalle);
            });
        })();
    </script>

@endsection

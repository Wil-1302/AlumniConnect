@extends('layouts.admin')

@section('titulo', 'Crear encuesta — Alumni Connect EFPISC')

@section('contenido')

    <a href="{{ route('admin.encuestas.index') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver a encuestas
    </a>

    <h1 class="mt-2 text-2xl font-bold text-slate-900">Crear nueva encuesta</h1>

    @php
        $erroresPreguntas = collect($errors->keys())->filter(fn ($clave) => str_starts_with($clave, 'preguntas'));
    @endphp

    @if ($erroresPreguntas->isNotEmpty())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Revise las preguntas:</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                @foreach ($erroresPreguntas as $clave)
                    <li>{{ $errors->first($clave) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.encuestas.guardar') }}" id="form-encuesta" class="mt-6 max-w-3xl space-y-6">
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="titulo" class="block text-sm font-medium text-slate-700">Título</label>
                <input type="text" name="titulo" id="titulo" required maxlength="150" value="{{ old('titulo') }}"
                       class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                              {{ $errors->has('titulo') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-slate-700">
                    Descripción <span class="font-normal text-slate-400">(opcional)</span>
                </label>
                <textarea name="descripcion" id="descripcion" rows="2"
                          class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                                 {{ $errors->has('descripcion') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-slate-700">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" required value="{{ old('fecha_inicio') }}"
                       class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                              {{ $errors->has('fecha_inicio') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-slate-700">Fecha de fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" required value="{{ old('fecha_fin') }}"
                       class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                              {{ $errors->has('fecha_fin') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Preguntas</h2>
                <button type="button" id="btn-agregar-pregunta"
                        class="rounded-md border border-institucional-700 px-3 py-1.5 text-sm font-medium text-institucional-700 hover:bg-institucional-50">
                    + Agregar pregunta
                </button>
            </div>

            <div id="preguntas-contenedor" class="mt-4 space-y-4"></div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
                Crear encuesta
            </button>
            <a href="{{ route('admin.encuestas.index') }}"
               class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Cancelar
            </a>
        </div>
    </form>

    {{-- Plantillas para clonar preguntas y opciones por JavaScript (sin dependencias) --}}
    <template id="plantilla-pregunta">
        <div class="pregunta-item rounded-md border border-slate-200 bg-slate-50 p-4" data-indice="__INDICE__">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Pregunta <span class="numero-pregunta"></span></h3>
                <button type="button" class="btn-quitar-pregunta text-xs font-medium text-slate-500 hover:text-slate-800">
                    Quitar pregunta
                </button>
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium text-slate-700">Enunciado</label>
                <input type="text" name="preguntas[__INDICE__][enunciado]" required maxlength="300"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium text-slate-700">Tipo de pregunta</label>
                <select name="preguntas[__INDICE__][tipo]" class="campo-tipo mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                    <option value="opcion_multiple">Opción múltiple</option>
                    <option value="escala">Escala (1 a 5)</option>
                </select>
            </div>

            <div class="opciones-bloque mt-3">
                <p class="text-xs text-slate-500">Opciones de respuesta (mínimo 2)</p>
                <div class="opciones-contenedor mt-2 space-y-2" data-opcion-contador="0"></div>
                <button type="button" class="btn-agregar-opcion mt-2 text-xs font-medium text-institucional-700 hover:underline">
                    + Agregar opción
                </button>
            </div>

            <p class="bloque-escala mt-3 hidden text-xs text-slate-500">
                El egresado responderá en una escala numérica del 1 (más bajo) al 5 (más alto).
                No es necesario definir opciones para este tipo de pregunta.
            </p>
        </div>
    </template>

    <template id="plantilla-opcion">
        <div class="flex items-center gap-2">
            <input type="text" name="preguntas[__INDICE__][opciones][__OPCION__][texto]" maxlength="200"
                   placeholder="Texto de la opción"
                   class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
            <button type="button" class="btn-quitar-opcion text-xs font-medium text-slate-500 hover:text-slate-800">
                Quitar
            </button>
        </div>
    </template>

    <script>
        (function () {
            var contenedorPreguntas = document.getElementById('preguntas-contenedor');
            var plantillaPregunta = document.getElementById('plantilla-pregunta');
            var plantillaOpcion = document.getElementById('plantilla-opcion');
            var contadorPreguntas = 0;

            function renumerar() {
                var bloques = contenedorPreguntas.querySelectorAll('.pregunta-item');
                bloques.forEach(function (bloque, indice) {
                    bloque.querySelector('.numero-pregunta').textContent = indice + 1;
                });
            }

            function agregarOpcion(bloquePregunta) {
                var indicePregunta = bloquePregunta.dataset.indice;
                var contenedorOpciones = bloquePregunta.querySelector('.opciones-contenedor');
                var contadorOpciones = parseInt(contenedorOpciones.dataset.opcionContador, 10) || 0;

                var html = plantillaOpcion.innerHTML
                    .split('__INDICE__').join(indicePregunta)
                    .split('__OPCION__').join(contadorOpciones);

                var envoltorio = document.createElement('div');
                envoltorio.innerHTML = html.trim();
                var nodoOpcion = envoltorio.firstElementChild;

                nodoOpcion.querySelector('.btn-quitar-opcion').addEventListener('click', function () {
                    nodoOpcion.remove();
                });

                contenedorOpciones.appendChild(nodoOpcion);
                contenedorOpciones.dataset.opcionContador = contadorOpciones + 1;
            }

            function alternarTipoPregunta(bloquePregunta) {
                var tipo = bloquePregunta.querySelector('.campo-tipo').value;
                var bloqueOpciones = bloquePregunta.querySelector('.opciones-bloque');
                var bloqueEscala = bloquePregunta.querySelector('.bloque-escala');

                bloqueOpciones.classList.toggle('hidden', tipo === 'escala');
                bloqueEscala.classList.toggle('hidden', tipo !== 'escala');
            }

            function agregarPregunta() {
                var html = plantillaPregunta.innerHTML.split('__INDICE__').join(contadorPreguntas);

                var envoltorio = document.createElement('div');
                envoltorio.innerHTML = html.trim();
                var nodoPregunta = envoltorio.firstElementChild;

                nodoPregunta.querySelector('.btn-quitar-pregunta').addEventListener('click', function () {
                    nodoPregunta.remove();
                    renumerar();
                });

                nodoPregunta.querySelector('.campo-tipo').addEventListener('change', function () {
                    alternarTipoPregunta(nodoPregunta);
                });

                nodoPregunta.querySelector('.btn-agregar-opcion').addEventListener('click', function () {
                    agregarOpcion(nodoPregunta);
                });

                contenedorPreguntas.appendChild(nodoPregunta);
                contadorPreguntas++;
                renumerar();

                // Toda pregunta de opción múltiple necesita al menos 2 opciones.
                agregarOpcion(nodoPregunta);
                agregarOpcion(nodoPregunta);
            }

            document.getElementById('btn-agregar-pregunta').addEventListener('click', agregarPregunta);

            // Arranca con una primera pregunta ya lista, para no entregar un
            // formulario vacío.
            agregarPregunta();
        })();
    </script>

@endsection

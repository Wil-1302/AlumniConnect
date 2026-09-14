@extends('layouts.app')

@section('titulo', $encuesta->titulo . ' — Alumni Connect EFPISC')

@section('contenido')

    <a href="{{ route('egresado.encuestas.index') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver a encuestas
    </a>

    <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $encuesta->titulo }}</h1>
    @if ($encuesta->descripcion)
        <p class="mt-1 text-sm text-slate-600">{{ $encuesta->descripcion }}</p>
    @endif
    <p class="mt-1 text-xs text-slate-400">Disponible hasta el {{ $encuesta->fecha_fin->format('d/m/Y') }}</p>

    <form method="POST" action="{{ route('egresado.encuestas.responder', $encuesta->id) }}" class="mt-6 max-w-2xl space-y-5">
        @csrf

        @foreach ($encuesta->preguntas as $indice => $pregunta)
            <fieldset class="rounded-lg border border-slate-200 bg-white p-5">
                <legend class="px-1 text-sm font-semibold text-slate-800">
                    {{ $indice + 1 }}. {{ $pregunta->enunciado }}
                </legend>

                @if ($pregunta->tipo === 'opcion_multiple')
                    <div class="mt-3 space-y-2">
                        @foreach ($pregunta->opciones as $opcion)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="radio" name="respuestas[{{ $pregunta->id }}][opcion_id]" value="{{ $opcion->id }}"
                                       @checked((string) old("respuestas.{$pregunta->id}.opcion_id") === (string) $opcion->id)
                                       class="border-slate-300 text-institucional-600 focus:ring-institucional-500">
                                {{ $opcion->texto }}
                            </label>
                        @endforeach
                    </div>
                    @error("respuestas.{$pregunta->id}.opcion_id")
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <div class="mt-3 flex flex-wrap items-center gap-4">
                        @for ($valor = 1; $valor <= 5; $valor++)
                            <label class="flex items-center gap-1.5 text-sm text-slate-700">
                                <input type="radio" name="respuestas[{{ $pregunta->id }}][valor_escala]" value="{{ $valor }}"
                                       @checked((string) old("respuestas.{$pregunta->id}.valor_escala") === (string) $valor)
                                       class="border-slate-300 text-institucional-600 focus:ring-institucional-500">
                                {{ $valor }}
                            </label>
                        @endfor
                    </div>
                    <p class="mt-1 text-xs text-slate-400">1 = más bajo, 5 = más alto</p>
                    @error("respuestas.{$pregunta->id}.valor_escala")
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </fieldset>
        @endforeach

        @error('respuestas')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-md bg-institucional-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
            Enviar respuestas
        </button>
    </form>

@endsection

@extends('layouts.app')

@section('titulo', 'Encuestas — Alumni Connect EFPISC')

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Encuestas de seguimiento</h1>
    <p class="mt-1 text-sm text-slate-500">
        Tu opinión ayuda a la Escuela a mejorar sus programas de formación.
    </p>

    @if ($encuestas->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">No tienes encuestas pendientes por responder.</p>
            <p class="mt-1 text-sm text-slate-500">Cuando la Escuela publique una nueva, aparecerá aquí.</p>
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($encuestas as $encuesta)
                <article class="flex flex-col rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="text-base font-semibold text-slate-900">{{ $encuesta->titulo }}</h2>
                    @if ($encuesta->descripcion)
                        <p class="mt-1 flex-1 text-sm text-slate-600">{{ $encuesta->descripcion }}</p>
                    @endif
                    <p class="mt-3 text-xs text-slate-400">
                        Disponible hasta el {{ $encuesta->fecha_fin->format('d/m/Y') }}
                    </p>
                    <a href="{{ route('egresado.encuestas.responder', $encuesta->id) }}"
                       class="mt-4 w-full rounded-md bg-institucional-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-institucional-800">
                        Responder
                    </a>
                </article>
            @endforeach
        </div>
    @endif

@endsection

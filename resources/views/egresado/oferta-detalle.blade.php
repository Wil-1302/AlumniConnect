@extends('layouts.app')

@section('titulo', $oferta->titulo . ' — Alumni Connect EFPISC')

@section('contenido')

    @php
        $etiquetaModalidad = ['presencial' => 'Presencial', 'remoto' => 'Remoto', 'hibrido' => 'Híbrido'][$oferta->modalidad] ?? $oferta->modalidad;
        $vigente = $oferta->estado === 'vigente';
    @endphp

    <a href="{{ route('egresado.ofertas') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver a la bolsa de trabajo
    </a>

    <article class="mt-4 rounded-lg border border-slate-200 bg-white p-6 sm:p-8">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-block rounded-full bg-institucional-50 px-2.5 py-0.5 text-xs font-medium text-institucional-700">
                {{ $etiquetaModalidad }}
            </span>
            @unless ($vigente)
                <span class="inline-block rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                    Esta oferta ya no está vigente
                </span>
            @endunless
        </div>

        <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $oferta->titulo }}</h1>
        <p class="mt-1 text-base text-slate-600">{{ $oferta->empresa }}</p>
        <p class="mt-1 text-sm text-slate-400">{{ $oferta->rubro?->nombre ?? 'Sin rubro específico' }}</p>

        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-1 text-sm text-slate-500 sm:grid-cols-2">
            <div><dt class="inline font-medium text-slate-600">Publicada:</dt> <dd class="inline">{{ $oferta->fecha_publicacion->format('d/m/Y') }}</dd></div>
            <div><dt class="inline font-medium text-slate-600">Cierra:</dt> <dd class="inline">{{ $oferta->fecha_cierre->format('d/m/Y') }}</dd></div>
        </dl>

        <div class="mt-6">
            <h2 class="text-sm font-semibold text-slate-900">Descripción</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $oferta->descripcion }}</p>
        </div>

        @if ($oferta->requisitos)
            <div class="mt-6">
                <h2 class="text-sm font-semibold text-slate-900">Requisitos</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $oferta->requisitos }}</p>
            </div>
        @endif

        <div class="mt-8 rounded-md border border-institucional-100 bg-institucional-50 p-4 text-sm text-institucional-800">
            Para postularte, comunícate directamente con <strong>{{ $oferta->empresa }}</strong> haciendo
            referencia a esta publicación de Alumni Connect EFPISC.
        </div>
    </article>

@endsection

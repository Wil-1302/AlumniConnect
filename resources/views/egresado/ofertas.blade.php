@extends('layouts.app')

@section('titulo', 'Bolsa de trabajo — Alumni Connect EFPISC')

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Bolsa de trabajo</h1>
    <p class="mt-1 text-sm text-slate-500">Ofertas laborales vigentes para egresados de la Escuela.</p>

    {{-- Filtros: se conservan al paginar mediante appends() --}}
    <form method="GET" action="{{ route('egresado.ofertas') }}"
          class="mt-6 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="rubro_id" class="block text-sm font-medium text-slate-700">Rubro</label>
            <select name="rubro_id" id="rubro_id"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                <option value="">Todos los rubros</option>
                @foreach ($rubros as $rubro)
                    <option value="{{ $rubro->id }}" @selected((string) ($filtros['rubro_id'] ?? '') === (string) $rubro->id)>
                        {{ $rubro->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1">
            <label for="modalidad" class="block text-sm font-medium text-slate-700">Modalidad</label>
            <select name="modalidad" id="modalidad"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                <option value="">Todas las modalidades</option>
                <option value="presencial" @selected(($filtros['modalidad'] ?? '') === 'presencial')>Presencial</option>
                <option value="remoto" @selected(($filtros['modalidad'] ?? '') === 'remoto')>Remoto</option>
                <option value="hibrido" @selected(($filtros['modalidad'] ?? '') === 'hibrido')>Híbrido</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
                Filtrar
            </button>
            @if (! empty($filtros['rubro_id']) || ! empty($filtros['modalidad']))
                <a href="{{ route('egresado.ofertas') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    @if ($ofertas->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">No hay ofertas laborales vigentes por ahora.</p>
            <p class="mt-1 text-sm text-slate-500">
                @if (! empty($filtros['rubro_id']) || ! empty($filtros['modalidad']))
                    Intenta con otros filtros, o vuelve a revisar más adelante.
                @else
                    Vuelve a revisar esta sección más adelante: se publican nuevas ofertas continuamente.
                @endif
            </p>
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($ofertas as $oferta)
                @php
                    $diasRestantes = now()->startOfDay()->diffInDays($oferta->fecha_cierre);
                    $etiquetaModalidad = ['presencial' => 'Presencial', 'remoto' => 'Remoto', 'hibrido' => 'Híbrido'][$oferta->modalidad] ?? $oferta->modalidad;
                @endphp
                <article class="flex flex-col rounded-lg border border-slate-200 bg-white p-5">
                    <span class="inline-block w-fit rounded-full bg-institucional-50 px-2.5 py-0.5 text-xs font-medium text-institucional-700">
                        {{ $etiquetaModalidad }}
                    </span>

                    <h2 class="mt-2 text-base font-semibold text-slate-900">
                        <a href="{{ route('egresado.ofertas.detalle', $oferta->id) }}" class="hover:underline">
                            {{ $oferta->titulo }}
                        </a>
                    </h2>
                    <p class="text-sm text-slate-600">{{ $oferta->empresa }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $oferta->rubro?->nombre ?? 'Sin rubro específico' }}</p>

                    <p class="mt-3 line-clamp-3 flex-1 text-sm text-slate-600">{{ $oferta->descripcion }}</p>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <span>Cierra el {{ $oferta->fecha_cierre->format('d/m/Y') }}</span>
                        <span class="font-medium {{ $diasRestantes <= 3 ? 'text-red-600' : 'text-institucional-700' }}">
                            @if ($diasRestantes === 0)
                                Cierra hoy
                            @elseif ($diasRestantes === 1)
                                Cierra mañana
                            @else
                                Quedan {{ $diasRestantes }} días
                            @endif
                        </span>
                    </div>

                    <a href="{{ route('egresado.ofertas.detalle', $oferta->id) }}"
                       class="mt-3 inline-block rounded-md bg-institucional-700 px-4 py-2 text-center text-sm font-semibold text-white transition-colors duration-150 hover:bg-institucional-800">
                        Ver detalle
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $ofertas->appends($filtros)->links() }}
        </div>
    @endif

@endsection

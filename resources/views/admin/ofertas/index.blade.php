@extends('layouts.admin')

@section('titulo', 'Ofertas laborales — Alumni Connect EFPISC')

@section('contenido')

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Ofertas laborales</h1>
        <a href="{{ route('admin.ofertas.crear') }}"
           class="w-full rounded-md bg-institucional-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
            Publicar nueva oferta
        </a>
    </div>

    @if ($ofertas->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">Todavía no se ha publicado ninguna oferta laboral.</p>
            <p class="mt-1 text-sm text-slate-500">Usa el botón "Publicar nueva oferta" para crear la primera.</p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Título</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Empresa</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Cierre</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Estado</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($ofertas as $oferta)
                        @php
                            // Derivado únicamente para mostrar una etiqueta; no es una regla de
                            // negocio nueva, replica en la vista la misma condición que ya aplica
                            // OfertaLaboral::scopeVigentes() sobre estos mismos datos ya cargados.
                            $estado = match (true) {
                                ! $oferta->activa => 'desactivada',
                                $oferta->fecha_cierre->lt(now()->startOfDay()) => 'cerrada',
                                default => 'vigente',
                            };
                            $estiloEstado = [
                                'vigente'     => 'bg-institucional-50 text-institucional-700',
                                'cerrada'     => 'bg-slate-100 text-slate-600',
                                'desactivada' => 'bg-slate-200 text-slate-500',
                            ][$estado];
                            $etiquetaEstado = ['vigente' => 'Vigente', 'cerrada' => 'Cerrada', 'desactivada' => 'Desactivada'][$estado];
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $oferta->titulo }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $oferta->empresa }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $oferta->fecha_cierre->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estiloEstado }}">
                                    {{ $etiquetaEstado }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($oferta->activa)
                                    <form method="POST" action="{{ route('admin.ofertas.desactivar', $oferta->id) }}"
                                          onsubmit="return confirm('¿Desactivar la oferta &quot;{{ $oferta->titulo }}&quot;? Dejará de mostrarse a los egresados.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                            Desactivar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $ofertas->links() }}
        </div>
    @endif

@endsection

@extends('layouts.admin')

@section('titulo', 'Reportes — Alumni Connect EFPISC')

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Reporte consolidado de egresados</h1>
    <p class="mt-1 text-sm text-slate-500">RF-23 / RF-24 / RF-25 — consolidado con filtros, exportable a Excel o PDF.</p>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.reportes') }}"
          class="mt-6 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <div class="w-full sm:w-32">
            <label for="anio_egreso" class="block text-sm font-medium text-slate-700">Promoción</label>
            <input type="number" name="anio_egreso" id="anio_egreso" placeholder="Ej: 2022"
                   min="2000" max="{{ now()->year }}"
                   value="{{ $filtros['anio_egreso'] ?? '' }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
        </div>

        <div class="w-full flex-1 sm:min-w-[12rem]">
            <label for="rubro" class="block text-sm font-medium text-slate-700">Rubro</label>
            <select name="rubro" id="rubro"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                <option value="">Todos</option>
                @foreach ($rubros as $rubro)
                    <option value="{{ $rubro->nombre }}" @selected(($filtros['rubro'] ?? '') === $rubro->nombre)>
                        {{ $rubro->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
                Filtrar
            </button>
            @if (! empty(array_filter($filtros)))
                <a href="{{ route('admin.reportes') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Descargas: conservan los filtros actuales --}}
    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('admin.reportes.exportar.excel', $filtros) }}"
           class="rounded-md border border-institucional-700 px-4 py-2 text-center text-sm font-semibold text-institucional-700 hover:bg-institucional-50">
            Descargar Excel (.xlsx)
        </a>
        <a href="{{ route('admin.reportes.exportar.pdf', $filtros) }}"
           class="rounded-md border border-institucional-700 px-4 py-2 text-center text-sm font-semibold text-institucional-700 hover:bg-institucional-50">
            Descargar PDF
        </a>
    </div>

    {{-- Vista previa --}}
    @if ($datos->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">No hay registros con estos filtros.</p>
            <p class="mt-1 text-sm text-slate-500">Ajusta los filtros o descarga igualmente un reporte vacío.</p>
        </div>
    @else
        <p class="mt-6 text-sm text-slate-500">
            Vista previa &middot; {{ $datos->count() }} {{ $datos->count() === 1 ? 'registro' : 'registros' }}
        </p>
        <div class="mt-2 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Nombre</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Promoción</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Grado</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Situación</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Rubro</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Empresa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($datos->take(50) as $fila)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $fila->apellidos }}, {{ $fila->nombres }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fila->anio_egreso }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fila->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fila->situacion ?? 'No registrada' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fila->rubro ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fila->empresa ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($datos->count() > 50)
            <p class="mt-2 text-xs text-slate-400">
                Mostrando los primeros 50 de {{ $datos->count() }} registros. Descarga el archivo para ver el resto.
            </p>
        @endif
    @endif

@endsection

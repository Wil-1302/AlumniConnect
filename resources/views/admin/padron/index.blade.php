@extends('layouts.admin')

@section('titulo', 'Padrón de egresados — Alumni Connect EFPISC')

@section('contenido')

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Padrón de egresados</h1>
            <p class="mt-1 text-sm text-slate-500">
                Registro oficial provisto por Secretaría Académica. {{ $registros->total() }} registros en total.
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.padron.plantilla') }}"
               class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50">
                Descargar plantilla
            </a>
            @if (auth()->user()->esAdminPrincipal())
                <a href="{{ route('admin.padron.importar') }}"
                   class="rounded-md bg-institucional-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-institucional-800">
                    Importar padrón
                </a>
            @endif
        </div>
    </div>

    @if (session('detalle_omitidos') && count(session('detalle_omitidos')) > 0)
        <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-semibold">Filas omitidas en la última importación:</p>
            <ul class="mt-1 max-h-40 list-disc space-y-1 overflow-y-auto pl-5 text-slate-600">
                @foreach (session('detalle_omitidos') as $omision)
                    <li>Fila {{ $omision['fila'] }} (DNI {{ $omision['dni'] ?: '—' }}): {{ $omision['motivo'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.padron.index') }}" class="mt-6 flex gap-2">
        <input type="text" name="busqueda" value="{{ $busqueda }}"
               placeholder="Buscar por DNI, nombre o año de egreso"
               class="block w-full max-w-md rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
        <button type="submit"
                class="rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
            Buscar
        </button>
        @if ($busqueda)
            <a href="{{ route('admin.padron.index') }}"
               class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Limpiar
            </a>
        @endif
    </form>

    @if ($registros->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">
                @if ($busqueda)
                    No se encontraron registros del padrón con esos criterios.
                @else
                    Todavía no se ha cargado el padrón de egresados.
                @endif
            </p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">DNI</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Nombres</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Apellidos</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Promoción</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Grado</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Cuenta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($registros as $registro)
                        <tr>
                            <td class="px-4 py-3 font-mono text-slate-800">{{ $registro->dni }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $registro->nombres }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $registro->apellidos }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $registro->anio_egreso }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $registro->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($registro->tiene_cuenta)
                                    <span class="inline-block rounded-full bg-institucional-50 px-2.5 py-0.5 text-xs font-medium text-institucional-700">
                                        Ya se registró
                                    </span>
                                @else
                                    <span class="inline-block rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                        Sin cuenta
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $registros->links() }}
        </div>
    @endif

@endsection

@extends('layouts.admin')

@section('titulo', 'Directorio — Alumni Connect EFPISC')

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Directorio de egresados</h1>
    <p class="mt-1 text-sm text-slate-500">
        Solo se muestran egresados con el perfil visible. Por la regla RN-07, no se
        muestran datos de contacto (correo ni teléfono).
    </p>

    {{-- Filtros combinables: se conservan al paginar mediante appends() --}}
    <form method="GET" action="{{ route('admin.directorio') }}"
          class="mt-6 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row sm:items-end sm:flex-wrap">
        <div class="w-full sm:w-32">
            <label for="anio_egreso" class="block text-sm font-medium text-slate-700">Promoción</label>
            <input type="number" name="anio_egreso" id="anio_egreso" placeholder="Ej: 2022"
                   min="2000" max="{{ now()->year }}"
                   value="{{ $filtros['anio_egreso'] ?? '' }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
        </div>

        <div class="w-full flex-1 sm:min-w-[12rem]">
            <label for="situacion_id" class="block text-sm font-medium text-slate-700">Situación</label>
            <select name="situacion_id" id="situacion_id"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                <option value="">Todas</option>
                @foreach ($situaciones as $situacion)
                    <option value="{{ $situacion->id }}" @selected((string) ($filtros['situacion_id'] ?? '') === (string) $situacion->id)>
                        {{ $situacion->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full flex-1 sm:min-w-[12rem]">
            <label for="rubro_id" class="block text-sm font-medium text-slate-700">Rubro</label>
            <select name="rubro_id" id="rubro_id"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-institucional-500 focus:outline-none focus:ring-1 focus:ring-institucional-500">
                <option value="">Todos</option>
                @foreach ($rubros as $rubro)
                    <option value="{{ $rubro->id }}" @selected((string) ($filtros['rubro_id'] ?? '') === (string) $rubro->id)>
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
                <a href="{{ route('admin.directorio') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    @if ($egresados->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">No se encontraron egresados con estos criterios.</p>
            <p class="mt-1 text-sm text-slate-500">Prueba ajustando o quitando los filtros.</p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Nombre</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Promoción</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Grado</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Situación</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Empresa</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Cargo</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Última actualización</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($egresados as $egresado)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $egresado->nombre_completo }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $egresado->anio_egreso }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $egresado->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $egresado->experienciaActual?->situacion?->nombre ?? 'No registrada' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $egresado->experienciaActual?->empresa ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $egresado->experienciaActual?->cargo ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $egresado->actualizado_en?->format('d/m/Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $egresados->appends($filtros)->links() }}
        </div>
    @endif

@endsection
